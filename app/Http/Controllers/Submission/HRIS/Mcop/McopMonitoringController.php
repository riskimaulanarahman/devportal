<?php

namespace App\Http\Controllers\Submission\HRIS\MCOP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Csv\Reader;
use DB;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderMail;

class McopMonitoringController extends Controller
{
    public $modulename;

    public function __construct()
    {
        $this->modulename = 'Mcop';
    }

    public function index() {
        try {
            $columns = [
                'e.SAP_ID',
                'e.Name',
                'e.Position',
                'e.Dept',
                'e.Estate',
                'e.Eligible',
                'v.Vehicle_Type',
                'v.Number_Plate_Old',
                'v.Number_Plate_New',
                'v.PO_Order',
                'v.Unit_From',
                'v.Date_of_Receipt',
                'v.SAP_Asset_Number',
                'v.Machine_No',
                'v.Frame_Number',
                'v.Vehicle_Accessories',
                'v.Unit_Build_Year',
                'v.Unit_Condition',
                'c.Contract_No',
                'c.Unit_Handover_Date',
                'c.Unit_Handover_No',
                'c.Unit_Price',
                'c.Period_Months',
                'c.Former_Owner',
                'c.Book_Value',
                'c.Previous_Contract_Ended_Date',
                'c.New_Contract_Value',
                'c.Monthly_Fuel_Subsidy',
                'c.Start_Contract',
                'c.End_Contract',
                'c.Remarks',
                'c.SPH',
                'r.Recondition_Start_Date',
                'r.Recondition_End_Date',
                'r.Recondition_Cost',
                'r.Recondition_Contract_Value',
                's.STNK_No',
                's.STNK_Expiry_Date',
                's.STNK_Tax',
                's.STNK_Position',
                'b.BPKB_No',
                'b.BPKB_Position',
            ];

            // Tambahkan alias untuk nama property
            $selects = [];
            foreach ($columns as $col) {
                $alias = str_replace('.', '_', $col);
                $selects[] = "$col as $alias";
            }

            $query = DB::table('mcop_Employee as e')
                ->leftJoin('mcop_Contract as c', 'e.SAP_ID', '=', 'c.SAP_ID')
                ->leftJoin('mcop_Vehicle as v', 'c.Vehicle_ID', '=', 'v.Vehicle_ID')
                ->leftJoin('mcop_Recondition as r', function($join) {
                    $join->on('v.Vehicle_ID', '=', 'r.Vehicle_ID');
                })
                ->leftJoin('mcop_STNK as s', function($join) {
                    $join->on('v.Vehicle_ID', '=', 's.Vehicle_ID')
                        ->on('e.SAP_ID', '=', 's.SAP_ID');
                })
                ->leftJoin('mcop_BPKB as b', 'v.Vehicle_ID', '=', 'b.Vehicle_ID')
                ->selectRaw('e.id as id')
                ->selectRaw(implode(', ', $selects));

            $data = $query->get();

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function import() {
        return view('import.mcop');
    }

    private function convertDate($date)
    {
        if (!$date || trim($date) === '') return null;

        $parts = explode('/', $date);
        if (count($parts) === 3) {
            // Format: dd/mm/yyyy → yyyy-mm-dd
            return $parts[2] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        }

        return null; // Jika format tidak sesuai
    }

    public function importCsv(Request $request)
    {
        // Tingkatkan batasan waktu dan memori
        ini_set('max_execution_time', 300); // 300 detik (5 menit)
        ini_set('memory_limit', '512M'); // 512 MB

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        if (!$file) {
            return back()->with('error', 'File upload gagal!');
        }
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        // $path = $request->file('csv_file')->getRealPath();

        // $csv = Reader::createFromPath($path, 'r');
        $csv->setDelimiter(';'); // Ganti dengan delimiter yang sesuai

        // Ambil header
        $header = $csv->fetchOne(0);

        // Mulai membaca data dari baris kedua
        $records = $csv->setOffset(1)->fetchAll();

        // Pecah data menjadi batch
        $chunks = array_chunk($records, 50);

        try {
            DB::beginTransaction();

            foreach ($chunks as $chunk) {
                foreach ($chunk as $record) {
                    try {
                        // Pastikan jumlah kolom sesuai
                        if (count($header) !== count($record)) {
                            throw new \Exception("Jumlah kolom tidak sesuai pada baris | header: " . count($header) . " & record: " . count($record) . " \n " . implode(',', $record));
                        }

                        $data = array_combine($header, $record);

                        // Sanitasi data
                        $data = array_map(function ($value) {
                            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        }, $data);

                        DB::table('mcop_Employee')->updateOrInsert(
                            ['SAP_ID' => $data['SAP_ID']],
                            [
                                'Name' => $data['Name'],
                                'Position' => $data['Position'],
                                'Dept' => $data['Dept'],
                                'Estate' => $data['Estate'],
                                'Eligible' => $data['Eligible'],
                            ]
                        );

                        DB::table('mcop_Vehicle')->updateOrInsert(
                            ['Number_Plate_New' => $data['Number_Plate_New']],
                            [
                                'Vehicle_Type' => $data['Vehicle_Type'],
                                'Number_Plate_Old' => $data['Number_Plate_Old'],
                                'PO_Order' => $data['PO_Order'],
                                'Unit_From' => $data['Unit_From'],
                                'Date_of_Receipt' => $this->convertDate($data['Date_of_Receipt']),
                                'SAP_Asset_Number' => $data['SAP_Asset_Number'],
                                'Machine_No' => $data['Machine_No'],
                                'Frame_Number' => $data['Frame_Number'],
                                'Vehicle_Accessories' => $data['Vehicle_Accessories'],
                                'Unit_Build_Year' => $data['Unit_Build_Year'],
                                'Unit_Condition' => $data['Unit_Condition'],
                            ]
                        );

                        // Cari id vehicle by number plate new
                        $vehicle = DB::table('mcop_Vehicle')->where('Number_Plate_New', $data['Number_Plate_New'])->first();
                        $vehicleId = $vehicle->Vehicle_ID ?? null;
                        if (!$vehicleId) {
                            throw new \Exception("Vehicle id not found after upsert for plate: " . $data['Number_Plate_New']);
                        }
                        
                        DB::table('mcop_Contract')->updateOrInsert(
                            ['Contract_No' => $data['Contract_No']],
                            [
                                'SAP_ID' => $data['SAP_ID'],
                                'Vehicle_ID' => $vehicleId,
                                'Unit_Handover_Date' => $this->convertDate($data['Unit_Handover_Date']),
                                'Unit_Handover_No' => $data['Unit_Handover_No'],
                                'Unit_Price' => $data['Unit_Price'],
                                'Period_Months' => $data['Period_Months'],
                                'Former_Owner' => $data['Former_Owner'],
                                'Book_Value' => $data['Book_Value'],
                                'Previous_Contract_Ended_Date' => $data['Previous_Contract_Ended_Date'] ?: null,
                                'New_Contract_Value' => $data['New_Contract_Value'],
                                'Monthly_Fuel_Subsidy' => $data['Monthly_Fuel_Subsidy'],
                                'Start_Contract' => $this->convertDate($data['Start_Contract']) ?: null,
                                'End_Contract' => $this->convertDate($data['End_Contract']) ?: null,
                                'Remarks' => $data['Remarks'],
                                'SPH' => $data['SPH'],
                            ]
                        );

                        DB::table('mcop_Recondition')->updateOrInsert(
                            [
                                'Vehicle_ID' => $vehicleId,
                            ],
                            [
                                'Recondition_Start_Date' => $this->convertDate($data['Recondition_Start_Date']) ?: null,
                                'Recondition_End_Date' => $this->convertDate($data['Recondition_End_Date']) ?: null,
                                'Recondition_Cost' => $data['Recondition_Cost'],
                                'Recondition_Contract_Value' => $data['Recondition_Contract_Value'],
                            ]
                        );

                        DB::table('mcop_STNK')->updateOrInsert(
                            ['STNK_No' => $data['STNK_No']],
                            [
                                'SAP_ID' => $data['SAP_ID'],
                                'Vehicle_ID' => $vehicleId,
                                'STNK_Expiry_Date' => $this->convertDate($data['STNK_Expiry_Date']) ?: null,
                                'STNK_Tax' => $data['STNK_Tax'],
                                'STNK_Position' => $data['STNK_Position'],
                            ]
                        );

                        DB::table('mcop_BPKB')->updateOrInsert(
                            ['BPKB_No' => $data['BPKB_No']],
                            [
                                'Vehicle_ID' => $vehicleId,
                                'BPKB_Position' => $data['BPKB_Position'],
                            ]
                        );

                    } catch (\Exception $e) {
                        // Log error untuk baris tertentu
                        Log::error("Error pada baris: " . implode(',', $record) . " - " . $e->getMessage());
                        throw $e; // Lanjutkan ke exception handler utama
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil diimpor!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function exportComplexJoinCsv()
    {
        $columns = [
            'e.SAP_ID',
            'e.Name',
            'e.Position',
            'e.Dept',
            'e.Estate',
            'e.Eligible',
            'v.Vehicle_Type',
            'v.Number_Plate_Old',
            'v.Number_Plate_New',
            'v.PO_Order',
            'v.Unit_From',
            'v.Date_of_Receipt',
            'v.SAP_Asset_Number',
            'v.Machine_No',
            'v.Frame_Number',
            'v.Vehicle_Accessories',
            'v.Unit_Build_Year',
            'v.Unit_Condition',
            'c.Contract_No',
            'c.Unit_Handover_Date',
            'c.Unit_Handover_No',
            'c.Unit_Price',
            'c.Period_Months',
            'c.Former_Owner',
            'c.Book_Value',
            'c.Previous_Contract_Ended_Date',
            'c.New_Contract_Value',
            'c.Monthly_Fuel_Subsidy',
            'c.Start_Contract',
            'c.End_Contract',
            'c.Remarks',
            'c.SPH',
            'r.Recondition_Start_Date',
            'r.Recondition_End_Date',
            'r.Recondition_Cost',
            'r.Recondition_Contract_Value',
            's.STNK_No',
            's.STNK_Expiry_Date',
            's.STNK_Tax',
            's.STNK_Position',
            'b.BPKB_No',
            'b.BPKB_Position',
        ];

        // Tambahkan alias untuk nama property
        $selects = [];
        foreach ($columns as $col) {
            $alias = str_replace('.', '_', $col);
            $selects[] = "$col as $alias";
        }

        $query = DB::table('mcop_Employee as e')
            ->leftJoin('mcop_Contract as c', 'e.SAP_ID', '=', 'c.SAP_ID')
            ->leftJoin('mcop_Vehicle as v', 'c.Vehicle_ID', '=', 'v.Vehicle_ID')
            ->leftJoin('mcop_Recondition as r', function($join) {
                $join->on('v.Vehicle_ID', '=', 'r.Vehicle_ID');
            })
            ->leftJoin('mcop_STNK as s', function($join) {
                $join->on('v.Vehicle_ID', '=', 's.Vehicle_ID')
                    ->on('e.SAP_ID', '=', 's.SAP_ID');
            })
            ->leftJoin('mcop_BPKB as b', 'v.Vehicle_ID', '=', 'b.Vehicle_ID')
            ->selectRaw(implode(', ', $selects));

        // --- Di sini data diambil sekaligus ---
        $allData = $query->get();

        // dd($allData);

        $filename = 'mcop_export_data_' . now()->format('Ymd_His') . '.csv';
        $header = [
            'SAP_ID','Name','Position','Dept','Estate','Eligible',
            'Vehicle_Type','Number_Plate_Old','Number_Plate_New','PO_Order','Unit_From','Date_of_Receipt','SAP_Asset_Number',
            'Machine_No','Frame_Number','Vehicle_Accessories','Unit_Build_Year','Unit_Condition',
            'Contract_No','Unit_Handover_Date','Unit_Handover_No','Unit_Price','Period_Months','Former_Owner',
            'Book_Value','Previous_Contract_Ended_Date','New_Contract_Value','Monthly_Fuel_Subsidy',
            'Start_Contract','End_Contract','Remarks','SPH',
            'Recondition_Start_Date','Recondition_End_Date','Recondition_Cost','Recondition_Contract_Value',
            'STNK_No','STNK_Expiry_Date','STNK_Tax','STNK_Position',
            'BPKB_No','BPKB_Position',
        ];

        $callback = function() use ($allData, $columns, $header) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $header);

            foreach ($allData as $row) {
                $data = [];
                foreach ($columns as $col) {
                    $alias = str_replace('.', '_', $col);
                    $raw = $row->$alias ?? '';
                    $data[] = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                fputcsv($handle, $data);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function reminderNotificationMessage($module,$bu) {
        if($module == strToLower($this->modulename)) {
            
            if($bu == 'ihm') {
                $mcopContractIHM = DB::table('ihm_mcop_contract')->get();
                $mcopStnkIHM = DB::table('ihm_mcop_stnk')->get();
            }

            $getRecipient = DB::table('reference.tbl_mailrecipient')
            ->where('module',$this->modulename)
            ->where('isActive',1)
            ->where('company_list', 'like', '%' . $bu . '%')
            ->get();

            // Ambil list email recipient
            $recipientEmails = $getRecipient->pluck('email')->toArray();

            $mailData = [
                "bu" => 'IHM',
                "mcop" => ($bu == 'ihm') ? $mcopContractIHM : null,
                "stnk" => ($bu == 'ihm') ? $mcopStnkIHM : null,
                "email" => $recipientEmails, // kirim kepada creator
                "message" => $this->mailMessage()['mcopExpiredReminder'],
            ];
            $mailData['email'] = $recipientEmails;
            Mail::to($mailData['email'])->send(new ReminderMail($mailData,$this->modulename));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
