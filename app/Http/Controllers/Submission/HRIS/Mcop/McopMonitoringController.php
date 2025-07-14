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

    public function import() {
        return view('import.mcop');
    }

    public function importCsv(Request $request)
    {
        // Tingkatkan batasan waktu dan memori
        ini_set('max_execution_time', 300); // 300 detik (5 menit)
        ini_set('memory_limit', '512M'); // 512 MB

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();

        $csv = Reader::createFromPath($path, 'r');
        $csv->setDelimiter(';'); // Ganti dengan delimiter yang sesuai

        // Ambil header
        $header = $csv->fetchOne(0);

        // Mulai membaca data dari baris kedua
        $records = $csv->setOffset(1)->fetchAll();

        // Pecah data menjadi batch
        $chunks = array_chunk($records, 50);

        try {
            DB::beginTransaction();

            // Bersihkan tabel-tabel terlebih dahulu
            DB::table('mcop_Employee')->truncate();
            DB::table('mcop_Vehicle')->truncate();
            DB::table('mcop_Contract')->truncate();
            DB::table('mcop_Recondition')->truncate();
            DB::table('mcop_STNK')->truncate();
            DB::table('mcop_BPKB')->truncate();

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

                        // Simpan ke tabel Employee
                        DB::table('mcop_Employee')->insert([
                            'SAP_ID' => $data['SAP_ID'],
                            'Name' => $data['Name'],
                            'Position' => $data['Position'],
                            'Dept' => $data['Dept'],
                            'Estate' => $data['Estate'],
                            'Eligible' => $data['Eligible'],
                        ]);

                        // Simpan ke tabel Vehicle
                        $vehicleId = DB::table('mcop_Vehicle')->insertGetId([
                            'Vehicle_Type' => $data['Vehicle_Type'],
                            'Number_Plate_Old' => $data['Number_Plate_Old'],
                            'Number_Plate_New' => $data['Number_Plate_New'],
                            'PO_Order' => $data['PO_Order'],
                            'Unit_From' => $data['Unit_From'],
                            'Date_of_Receipt' => $data['Date_of_Receipt'],
                            'SAP_Asset_Number' => $data['SAP_Asset_Number'],
                            'Machine_No' => $data['Machine_No'],
                            'Frame_Number' => $data['Frame_Number'],
                            'Vehicle_Accessories' => $data['Vehicle_Accessories'],
                            'Unit_Build_Year' => $data['Unit_Build_Year'],
                            'Unit_Condition' => $data['Unit_Condition'],
                        ]);
                        
                        // Simpan ke tabel Contract
                        DB::table('mcop_Contract')->insert([
                            'SAP_ID' => $data['SAP_ID'],
                            'Vehicle_ID' => $vehicleId,
                            'Contract_No' => $data['Contract_No'],
                            'Unit_Handover_Date' => $data['Unit_Handover_Date'],
                            'Unit_Handover_No' => $data['Unit_Handover_No'],
                            'Unit_Price' => $data['Unit_Price'],
                            'Period_Months' => $data['Period_Months'],
                            'Former_Owner' => $data['Former_Owner'],
                            'Book_Value' => $data['Book_Value'],
                            'Previous_Contract_Ended_Date' => $data['Previous_Contract_Ended_Date'],
                            'New_Contract_Value' => $data['New_Contract_Value'],
                            'Monthly_Fuel_Subsidy' => $data['Monthly_Fuel_Subsidy'],
                            'Start_Contract' => $data['Start_Contract'],
                            'End_Contract' => $data['End_Contract'],
                            'Remarks' => $data['Remarks'],
                            'SPH' => $data['SPH'],
                        ]);

                        // Simpan ke tabel Recondition
                        DB::table('mcop_Recondition')->insert([
                            'Vehicle_ID' => $vehicleId,
                            'Recondition_Start_Date' => $data['Recondition_Start_Date'],
                            'Recondition_End_Date' => $data['Recondition_End_Date'],
                            'Recondition_Cost' => $data['Recondition_Cost'],
                            'Recondition_Contract_Value' => $data['Recondition_Contract_Value'],
                        ]);

                        // Simpan ke tabel STNK
                        DB::table('mcop_STNK')->insert([
                            'SAP_ID' => $data['SAP_ID'],
                            'Vehicle_ID' => $vehicleId,
                            'STNK_No' => $data['STNK_No'],
                            'STNK_Expiry_Date' => $data['STNK_Expiry_Date'],
                            'STNK_Tax' => $data['STNK_Tax'],
                            'STNK_Position' => $data['STNK_Position'],
                        ]);

                        // Simpan ke tabel BPKB
                        DB::table('mcop_BPKB')->insert([
                            'Vehicle_ID' => $vehicleId,
                            'BPKB_No' => $data['BPKB_No'],
                            'BPKB_Position' => $data['BPKB_Position'],
                        ]);
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

    public function reminderNotificationMessage($mode,$bu) {
        if($mode == 'reminder') {
            
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

    public function index()
    {
        //
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
