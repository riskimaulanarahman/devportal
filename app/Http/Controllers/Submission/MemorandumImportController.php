<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Csv\Reader;
use DB;
use Log;


class MemorandumImportController extends Controller
{
    public $modulename;

    public function __construct()
    {
        $this->modulename = 'Memorandum';
    }

    public function index() 
    {
        try {
            // Kolom yang ingin ditampilkan
            $columns = [
                'id',
                'fullName',
                'estate',
                'bu',
                'joinDate',
                'birthOfDate',
            ];

            // Tambahkan alias untuk nama property (opsional)
            $selects = [];
            foreach ($columns as $col) {
                $alias = str_replace('.', '_', $col);
                $selects[] = "$col as $alias";
            }

            // Query langsung ke employee.tbl_pkwt
            $query = DB::table('employee.tbl_pkwt')
                ->selectRaw(implode(', ', $selects))
                ->orderBy('id', 'desc');;

            $data = $query->get();
            
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }


    public function import() {
        return view('import.memorandum');
    }

    public function importCsv(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        if (!$file) {
            return back()->with('error', 'File upload gagal!');
        }

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setDelimiter(';');

        $header = $csv->fetchOne(0);
        $records = $csv->setOffset(1)->fetchAll();
        $chunks = array_chunk($records, 50);

        try {
            DB::beginTransaction();

            foreach ($chunks as $chunk) {
                foreach ($chunk as $record) {
                    try {
                        if (count($header) !== count($record)) {
                            throw new \Exception("Jumlah kolom tidak sesuai pada baris | header: " . count($header) . " & record: " . count($record) . " \n " . implode(',', $record));
                        }

                        $data = array_combine($header, $record);

                        // Sanitasi data
                        $data = array_map(function ($value) {
                            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        }, $data);

                        // Konversi tanggal langsung di sini
                        $joinDate = null;
                        if (!empty($data['joinDate'])) {
                            $parts = explode('/', $data['joinDate']); // asumsikan format dd/MM/yyyy
                            if (count($parts) === 3) {
                                $joinDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // yyyy-MM-dd
                            } else {
                                $joinDate = $data['joinDate']; // fallback
                            }
                        }
                        $rowIndexGlobal = 1; 
                            foreach ($records as $record) {
                                $data = array_combine($header, $record);
                                $baseId = (int) $data['employeeId'];
                                $uniqueId = ($baseId * 100000) + $rowIndexGlobal;
                                DB::table('employee.tbl_pkwt')->updateOrInsert(
                                    ['employeeId' => $uniqueId], 
                                    [   
                                        'fullname'       => $data['fullname'],
                                        'position'       => $data['position'],
                                        'estate'         => $data['estate'],
                                        'bu'             => $data['bu'],
                                        'joinDate'       => $joinDate,
                                        'birthOfDate'    => $data['birthOfDate'],
                                        'gender'         => $data['gender'],
                                        'religion'       => $data['religion'],
                                        'nik'            => $data['nik'],
                                        'kk'             => $data['kk'],
                                        'npwp'           => $data['npwp'],
                                        'bpjsKes'        => $data['bpjsKes'],
                                        'bpjsTk'         => $data['bpjsTk'],
                                        'noHp'           => $data['noHp'],
                                        'address'        => $data['address'],
                                        'maritalStatus'  => $data['maritalStatus'],
                                        'bank'           => $data['bank'],
                                        'noRekening'     => $data['noRekening'],
                                    ]
                                );
                                 $rowIndexGlobal++;
                            }
                    } catch (\Exception $e) {
                        Log::error("Error pada baris: " . implode(',', $record) . " - " . $e->getMessage());
                        throw $e;
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data kontraktor berhasil diimpor!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
