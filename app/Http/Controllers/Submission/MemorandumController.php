<?php

namespace App\Http\Controllers\Submission;

use DB;
use COM;
use Log;
use App\Models\User;
use App\Models\Module;

use App\Models\Employee;
use App\Models\Attachment;
use App\Models\Useraccess;
use App\Mail\SubmissionMail;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\MemorandumHis;
use Illuminate\Support\Carbon;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\Memorandum;

use function PHPUnit\Framework\fileExists;

class MemorandumController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new Memorandum();
        $this->modulename = 'Memorandum';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }
    public function index()
    {
        $data = DB::table('memoExp')->select('*')->get();   
        // dd($data);     
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);        
    }

    public function show($id)
    {
        try {
            $data = $this->model->where('id', $id)->first();
            if (!$data) {
                $memoExpData = DB::table('memoExp')->where('id', $id)->first();
    
                if (!$memoExpData) {
                    return response()->json(["status" => "error", "message" => "Data memoExp tidak ditemukan"]);
                }
    
                $existingRequest = DB::table('request_memorandum')->where('sysid', $memoExpData->sys_id)->first();
    
                if (!$existingRequest) {
                    // Jika tidak ada, buat data baru
                    $newId = DB::table('request_memorandum')->insertGetId([
                        'employee_id' => $memoExpData->id ?? null,
                        'requestStatus' => 0, 
                        'bu' => $memoExpData->bu,
                        'sysid' => $memoExpData->sys_id ?? null,
                        'code_id' => $this->generateCode($this->modulename),
                        'user_id' => $this->getAuth()->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
    
                    $data = $this->model->where('id', $newId)->first();
                } else {
                    $data = $existingRequest;
                    $user_id = $this->getAuth()->id;
                    $module_id = $this->getModuleId($this->modulename);
                    $subquery = "(SELECT TOP 1 CASE WHEN a.user_id = '".$user_id."' THEN 1 ELSE 0 END 
                    FROM tbl_approverListReq l
                    LEFT JOIN tbl_approver a ON l.approver_id = a.id
                    LEFT JOIN tbl_approvaltype r ON a.approvaltype_id = r.id 
                    WHERE l.ApprovalAction = '1' 
                    AND l.req_id = request_memorandum.id 
                    AND l.module_id = '".$module_id."' 
                    AND request_memorandum.requestStatus = '1'
                    ORDER BY a.sequence)";
                    $data = $this->model->selectRaw("
                            request_memorandum.*, codes.code,
                            CASE WHEN request_memorandum.user_id='".$user_id."' THEN 1 ELSE 0 END AS isMine,
                            ".$subquery." AS isPendingOnMe
                        ")
                        ->leftJoin('codes', 'request_memorandum.code_id', 'codes.id')
                        ->with(['user', 'approverlist'])
                        ->where('request_memorandum.id', $data->id)
                        ->first();
                }
            }
            $memoExpExtraData = DB::table('memoExp')->where('sys_id', $data->sysid)->first();
    
            if ($memoExpExtraData) {
                foreach ($memoExpExtraData as $key => $value) {
                    if (!isset($data->$key)) { // Pastikan hanya menambahkan data yang belum ada
                        $data->$key = $value;
                    }
                }
            }
    
            return response()->json([
                'status' => "show",
                'message' => "Data ditemukan atau baru dibuat",
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);
    
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    public function getList($modulename)
    {
        $data = DB::table('request_memorandum_his')
            ->join('request_memorandum', 'request_memorandum_his.req_id', '=', 'request_memorandum.id')
            ->select('request_memorandum_his.*', 'request_memorandum.id as reqid') // Ambil reqid dari master table
            ->get(); 

        return response()->json([
            'status' => "show",
            'message' => $this->getMessage()['show'],
            'data' => $data
        ])->setEncodingOptions(JSON_NUMERIC_CHECK);        
    }
    // {
    //     try {
    //         $user_id = $this->getAuth()->id;
    //         $module = $this->module->select('id', 'module')->where('module', $modulename)->first();
            
    //         if ($module) {
    //             $data = $this->model->selectRaw("
    //                 request_memorandum_his.*, 
    //                 codes.code,
    //                 CASE WHEN request_memorandum_his.user_id = ? THEN 1 ELSE 0 END AS isMine,
    //                 (SELECT TOP 1 CASE WHEN a.user_id = ? THEN 1 ELSE 0 END 
    //                 FROM tbl_approverListReq l
    //                 LEFT JOIN tbl_approver a ON l.approver_id = a.id
    //                 WHERE l.req_id = request_memorandum_his.id 
    //                 AND l.module_id = ? 
    //                 AND request_memorandum_his.requestStatus = '1'
    //                 ORDER BY a.sequence) AS isPendingOnMe
    //             ", [$user_id, $user_id, $module->id]) 
                
    //             ->leftJoin('codes', 'request_memorandum_his.code_id', '=', 'codes.id')
    //             ->with(['user', 'approverlist'])
    //             ->where('req_id', $id)
    //             ->orderBy('sequence', 'DESC')
    //             ->get();

    //             return response()->json([
    //                 "status" => "show", 
    //                 "message" => $this->getMessage()['show'], 
    //                 "data" => $data
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => "show", 
    //                 "message" => $this->getMessage()['errornotfound']
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         return response()->json(["status" => "error", "message" => $e->getMessage()]);
    //     }
    // }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->all();

            if (!isset($requestData['contractList']) || !is_array($requestData['contractList'])) {
                return response()->json(["status" => "error", "message" => "ContractList data is required and should be an array."]);
            }

            dd($requestData['contractList']);

            foreach ($requestData['contractList'] as $contract) {
                $updateData = array_filter($contract, function ($value) {
                    return $value !== null;
                });

                if (!empty($updateData)) {
                    MemorandumHis::where('id', $contract['memorandum_id'])
                        ->update($updateData);
                }
            }

            DB::commit();
            return response()->json(["status" => "success", "message" => "ContractList updated successfully"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari module berdasarkan nama modul
            $module = $this->module->select('id', 'module')->where('module', $this->modulename)->first();
            $user_id = $this->getAuth()->id;

            // Jika module ditemukan, lakukan delete secara atomik
            if ($module) {
                DB::transaction(function () use ($id, $module, $user_id) {
                    // Hapus data pada tabel ApproverListReq
                    ApproverListReq::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    ApproverListHistory::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    // Hapus data pada tabel utama
                    
                    $data = $this->model->where('id',$id)->where('requestStatus',0)->where('user_id',$user_id)->first();
                    if ($data) {
                        $data->delete();
                    } else {
                        throw new \Exception($this->getMessage()['errordestroysubmission']);
                    }
                    
                });

                return  response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

            } else {
                return  response()->json(["status" => "error", "message" => $this->getMessage()['modulenotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    
    public function genPdfmemorandumReq(Request $request, $id)
    {
        $dataAppr = DB::table('memoApprover')->select('*')->where('id',$id)->get(); 
        $data = $this->model->select(
                'request_memorandum.*',
                'codes.code',
                'employee.tbl_employee.FullName as FullName', 
                'employee.tbl_employee.sys_id as sys_id', 
                'employee.tbl_employee.SAPID as SAPID',
                'employee.tbl_employee.JoinDate as JoinDate', 
                'employee.tbl_employee.deptheadName as deptheadName',
                'employee.tbl_employee.contract_status as contract_status',
                'employee.tbl_employee.BirthOfDate as BirthOfDate',
                'employee.tbl_level.Level as Level',
                'employee.tbl_designation.DesignationName as DesignationName',
            )
            ->leftJoin('users', 'request_memorandum.user_id', 'users.id')
            ->leftJoin('codes', 'request_memorandum_his.code_id', '=', 'codes.id')
            ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
            ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
            ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
            ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
            ->where('request_memorandum.id', $id)
            ->first();
        if (!$data || !$data->approverHistory) {
            return response()->json(["status" => "error", "message" => "Data or approver history not found"]);
        }
        $contracts = DB::table('request_memorandum as rm_his')
        ->select('rm_his.sequence',
        'rm_his.startContract', 
        'rm_his.endContract',
        'rm_his.remarks')
        ->get();

        if ($contracts->isEmpty()) {
            \Log::warning('No contracts found for request ID: ' . $id);
        }
        $today = Carbon::today();
        $oldContracts = []; 
        $currentContracts = []; 
        $newContracts = []; 
        foreach ($contracts as $contract) {
            if ($contract->endContract < $today) {
                // Kontrak Lama: Berakhir sebelum hari ini
                $oldContracts[] = $contract;
            } elseif ($contract->startContract <= $today && $contract->endContract >= $today) {
                // Kontrak Sekarang: Sedang berlangsung hari ini
                $currentContracts[] = $contract;
            } elseif ($contract->startContract > $today) {
                // Kontrak Baru: Dimulai setelah hari ini
                $newContracts[] = $contract;
            }
        }
        try {
            $birthDate = Carbon::parse($data->BirthOfDate);
            $usia = $birthDate 
                ? Carbon::now()->year - $birthDate->year 
                    - (Carbon::now()->lessThan(Carbon::parse($birthDate)->addYears(Carbon::now()->year - $birthDate->year)) ? 1 : 0)
                : null;
            $excel = new \COM("Excel.Application") or die("ERROR: Unable to instantiate COM!\r\n");
            $excel->Visible = false;
            $file = ""; // Inisialisasi sebagai string kosong

            if ($data->contract_status === "Contract") {
                $file = public_path("template/memo/ihm-kontrak.xlsx");
            } elseif ($data->contract_status === "Permanent") {
                $file = public_path("template/memo/ihm-pensiun.xlsx");
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown contract status: ' . ($data->contract_status ?: 'NULL')
                ]);
            }

            if (!is_string($file) || empty($file) || !file_exists($file)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found: ' . ($file ?: 'Undefined file path')
                ]);
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets(1);
            $Worksheet->Activate;
            $Worksheet->Range("B10")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E15")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E18")->Value = ($data->FullName ?? ' ') . ' / ' . ($data->SAPID ?? ' '); 
            $Worksheet->Range("E21")->Value = $data->DesignationName ?? ''; 
            $Worksheet->Range("F21")->Value = $data->sys_id ?? ''; 
            $Worksheet->Range("C36")->Value = $data->remarks ?? ''; 
            $Worksheet->Range("C63")->Value = $data->DesignationName ?? ''; 
            $Worksheet->Range("A56")->Value = $data->superiorName ?? ''; 
            $Worksheet->Range("B56")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E39")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("C62")->Value = $data->Pendidikan ?? '-'; 
            $Worksheet->Range("G11")->Value = $data->code ?? '-'; 
            $Worksheet->Range("C64")->Value = $data->Usia ?? '-'; 
            $Worksheet->Range("C61")->Value = $data->BirthOfDate 
                ? Carbon::parse($data->BirthOfDate)->locale('id')->translatedFormat('j F Y')
                : '';
            $Worksheet->Range("C62")->Value = $usia ?? 'N/A';
            
            $Worksheet->Range("E24")->Value = $data->JoinDate 
                ? Carbon::parse($data->JoinDate)->locale('id')->translatedFormat('j F Y')
                : '';
            $Worksheet->Range("G10")->Value = $data->created_at 
                ? Carbon::parse($data->created_at)->locale('id')->translatedFormat('j F Y')
                : '';
            $startRow = 61; // Baris awal untuk kontrak
            $filteredContracts = array_merge($oldContracts, $currentContracts); // Gabungkan kontrak lama dan sekarang
            foreach ($filteredContracts as $index => $contract) {
                $row = $startRow + $index * 1;
                $Worksheet->Range("E{$row}")->Value = "Kontrak " . ($index + 1); // Label Kontrak
                $Worksheet->Range("F{$row}")->Value = $contract->startContract && $contract->endContract
                    ? Carbon::parse($contract->startContract)->format('d-m-Y') . ' - ' . Carbon::parse($contract->endContract)->format('d-m-Y')
                    : ' - '; // Periode Kontrak
            }
            if (!empty($currentContracts)) {
                $currentEndContract = $currentContracts[0]->endContract; // Ambil endContract dari kontrak aktif pertama
                $Worksheet->Range("E27")->Value = Carbon::parse($currentEndContract)->locale('id')->translatedFormat('j F Y'); // Format menjadi dd-month(string)-yyyy
            } else {
                $Worksheet->Range("E27")->Value = ' - '; // Jika tidak ada kontrak aktif
            }
            if (!empty($newContracts)) {
                $newStartContract = $newContracts[0]->startContract; // Ambil startContract dari kontrak baru pertama
                $newEndContract = $newContracts[0]->endContract; // Ambil endContract dari kontrak baru pertama
                $Worksheet->Range("D33")->Value = Carbon::parse($newStartContract)->locale('id')->translatedFormat('j F Y') . ' sampai dengan ' . Carbon::parse($newEndContract)->locale('id')->translatedFormat('j F Y'); // Isi di D33
            } else {
                $Worksheet->Range("D33")->Value = ' - '; // Jika tidak ada kontrak baru
            }

            $picpath = public_path("assets/images/approved.png");

            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel, $offset = 0) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top - $offset; // Offset untuk menggeser gambar ke atas
                $pic->Left = $excel->Cells($row, $column)->Left;
                
            }

            foreach ($dataAppr as $appr) {
                if ($appr->sequence == 1) {
                    if ($appr->approvalAction == 3) {
                        // $Worksheet->Range("A53")->Value = $appr->apprname;
                        // $Worksheet->Range("A54")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 55, 1, 40, $excel, 25);                        
                        addPictureToWorksheet($Worksheet, $picpath, 55, 3, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 4, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 5, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 7, 40, $excel, 25);
                    }
                }
                if ($appr->sequence == 2) {
                    if ($appr->approvalAction == 3) {
                        // $Worksheet->Range("E56")->Value = $appr->apprname;
                        // $Worksheet->Range("E57")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 55, 3, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 4, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 5, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 7, 40, $excel, 25);
                    }
                }
            }
            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/memo/pdf/' . $fileName);
			if (file_exists($filePath)) {
				unlink($filePath);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $filePath, $xlQualityStandard);
			
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
            $pathfilename = 'public/template/memo/pdf/' . $fileName;
            DB::table('request_memorandum')
            ->where('id', $id) 
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);

			return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-memorandum', $e->getMessage());
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}