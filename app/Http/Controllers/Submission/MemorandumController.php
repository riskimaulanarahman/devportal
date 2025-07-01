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
use App\Models\Submission\MemorandumDetail;
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
        try {
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);

            // Ambil semua data dari memoExp sebagai sumber
            $memos = DB::table('memoExp')->get();

            foreach ($memos as $memo) {
                $exists = DB::table('request_memorandum')
                            ->where('employee_id', $memo->id)
                            ->exists();

                if (!$exists) {
                    DB::table('request_memorandum')->insert([
                        'employee_id' => $memo->id,
                        'requestStatus' => 0,
                        'bu' => $memo->bu ?? null,
                        'sysid' => $memo->sys_id ?? null,
                        'code_id' => $this->generateCode($this->modulename),
                        'user_id' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // $getAccess = "(select TOP 1 CASE WHEN l.employee_id='".$user_id."' then 1 else 0 end 
            // from [authorization].tbl_useraccess l
            // where l.module_id = '".$module_id."' and l.allowView='1')";
            $getAccess = "(
                            SELECT CASE 
                                WHEN EXISTS (
                                    SELECT 1 
                                    FROM [authorization].tbl_useraccess l 
                                    WHERE l.module_id = '".$module_id."'
                                    AND l.allowView = '1'
                                    AND l.employee_id = '".$user_id."'
                                ) THEN 1 ELSE 0 
                            END
                        )";

            // Sekarang data sudah sinkron, tinggal ambil view gabungannya
            $data = DB::table('request_memorandum AS r')
                ->leftJoin('codes','r.code_id','codes.id')
                ->leftJoin('users AS u', 'r.user_id', '=', 'u.id')
                ->leftjoin('memoExp AS m', 'r.employee_id', '=', 'm.id')
                ->selectRaw("
                        r.*, 
                        codes.code,
                        m.FullName,
                        m.JoinDate,
                        m.BirthOfDate,
                        m.contract_status,
                        m.sys_id,
                        m.SAPID,
                        m.Location,
                        m.DesignationName,
                        m.bu,
                        m.end_contract_date,
                        m.retirement_date,
                        ".$getAccess." as isMine,
                        (
                            SELECT TOP 1 CASE WHEN a.user_id = ? THEN 1 ELSE 0 END
                            FROM tbl_approverListReq l
                            LEFT JOIN tbl_approver a ON l.approver_id = a.id
                            LEFT JOIN tbl_approvaltype t ON a.approvaltype_id = t.id 
                            WHERE l.ApprovalAction = '1' 
                            AND l.req_id = r.id 
                            AND l.module_id = ? 
                            ORDER BY a.sequence
                        ) AS isPendingOnMe
                    ", [$user_id, $module_id])
                    ->orderByDesc('r.id')
                    ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => "empty",
                    'message' => "Data tidak ditemukan, silakan tambahkan data baru",
                    'data' => []
                ])->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
        {
            try {

                $data = $this->model->select('request_memorandum.*',
                 'm.FullName',
                 'codes.code',
                 'm.JoinDate',
                 'm.BirthOfDate',
                 'm.contract_status',
                 'm.sys_id',
                 'm.SAPID',
                 'm.Location',
                 'm.DesignationName',
                 'm.bu')
                ->leftJoin('codes','request_memorandum.code_id','codes.id')
                ->leftjoin('memoExp AS m', 'request_memorandum.employee_id', '=', 'm.id')                
                ->where('request_memorandum.id',$id)
                ->with(['user', 'approverlist', 'request_memorandum_detail'])
                ->first();

                return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

            } catch (\Exception $e) {

                return response()->json(["status" => "error", "message" => $e->getMessage()]);
            }
        }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->model->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Additional approver berhasil ditambahkan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
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
            ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
            ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
            ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
            ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
            ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
            ->where('request_memorandum.id', $id)
            ->first();
        if (!$data || !$data->approverHistory) {
            return response()->json(["status" => "error", "message" => "Data or approver history not found"]);
        }

        $contracts = DB::table('request_memorandum_detail')
            ->leftJoin('codes', 'request_memorandum_detail.code_id', '=', 'codes.id')
            ->select('request_memorandum_detail.*', 'codes.code') // ambil kolom code
            ->where('req_id', $id)
            ->orderBy('sequence')
            ->get();

        $hisCreated = $contracts->last()->created_at ?? null;
        $lastRemarks = $contracts->last()->remarks ?? '-';
        $lastCode = $contracts->last()->code ?? '-';     

        if ($contracts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "kontrak kosng untuk $id"
            ]);
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
            $file = ""; 
            $file = public_path("template/memo/template.xlsx"); 

            $bu = strtoupper(trim($data->bu)); 
            $status = strtoupper(trim($data->contract_status)); 

            $statusMap = [
                'CONTRACT' => 'KONTRAK',
                'PERMANENT' => 'PENSIUN'
            ];

            // Validasi status
            if (!array_key_exists($status, $statusMap)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown contract status: ' . ($data->contract_status ?: 'NULL')
                ]);
            }
            $sheetName = "{$bu}_{$statusMap[$status]}"; 

            // Validasi file template
            if (!file_exists($file)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File template tidak ditemukan: ' . $file
                ]);
            }
            if (!is_string($file) || empty($file) || !file_exists($file)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found: ' . ($file ?: 'Undefined file path')
                ]);
            }
            $today = Carbon::today();

            $currentStartContract = null;
            $currentEndContract   = null;
            $newStartContract     = null;
            $newEndContract       = null;

            // Jika status Permanent, langsung set tanggal pensiun sebagai akhir masa kerja
            if ($data->contract_status === "Permanent") {
                $birthDate = $data->BirthOfDate ? Carbon::parse($data->BirthOfDate) : null;
                $currentEndContract = $birthDate ? $birthDate->copy()->addYears(55) : null;
            }

            // Loop kontrak untuk cari kontrak aktif dan kontrak baru
            foreach ($contracts as $contract) {
                $start = Carbon::parse($contract->startContract);
                $end   = Carbon::parse($contract->endContract);

                if ($data->contract_status === "Contract" && $start->lt($today) && $end->gt($today)) {
                    $currentStartContract = $start;
                    $currentEndContract   = $end;
                }

                if ($start->gt($today) && !$newStartContract && !$newEndContract) {
                    $newStartContract = $start;
                    $newEndContract   = $end;
                }
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets($sheetName);
            $Worksheet->Activate();

            $Worksheet->Range("A53")->Value = $data->superiorName ?? ''; 

            $Worksheet->Range("B8")->Value = $data->deptheadName ?? ''; 

            $Worksheet->Range("D34")->Value = $lastRemarks;            
            $Worksheet->Range("C59")->Value = $data->Pendidikan ?? '-'; 
            $Worksheet->Range("C58")->Value = $data->DesignationName ?? ''; 
            $Worksheet->Range("C56")->Value = $data->BirthOfDate 
                ? Carbon::parse($data->BirthOfDate)->locale('id')->translatedFormat('j F Y')
                : '';
            $Worksheet->Range("C57")->Value = $usia ?? 'N/A';
                
            // Tulis kontrak baru ke sel D33
            $Worksheet->Range("D31")->Value = ($newStartContract && $newEndContract)
                ? 'Dari ' . $newStartContract->locale('id')->translatedFormat('j F Y') .
                ' sampai dengan ' . $newEndContract->locale('id')->translatedFormat('j F Y')
                : ' - ';

            // Jika status Contract, cetak daftar kontrak lama & aktif ke Excel
            if ($data->contract_status === "Contract") {
                $filteredContracts = array_merge($oldContracts, $currentContracts);

                $startRow = 56;
                foreach ($filteredContracts as $index => $contract) {
                    $row = $startRow + $index;
                    $range = $Worksheet->Range("D{$row}");

                    $label = "Kontrak " . ($index + 1) . " :";
                    $period = ($contract->startContract && $contract->endContract)
                        ? Carbon::parse($contract->startContract)->format('d-m-Y') . ' - ' .
                        Carbon::parse($contract->endContract)->format('d-m-Y')
                        : ' - ';

                    $range->Value = "{$label} {$period}";
                }
            }
            $Worksheet->Range("E13")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E16")->Value = ($data->FullName ?? ' ') . ' / ' . ($data->SAPID ?? ' '); 
            $Worksheet->Range("E19")->Value = $data->DesignationName ?? '-'; 
            $Worksheet->Range("E37")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E22")->Value = $data->JoinDate 
                ? Carbon::parse($data->JoinDate)->locale('id')->translatedFormat('j F Y')
                : '';
            // Tulis akhir masa kerja ke sel E27
            if ($currentEndContract) {
                $sequence = $currentEndContract->sequence ?? '-';
                $tanggal = $currentEndContract->locale('id')->translatedFormat('j F Y');

                $Worksheet->Range("E25")->Value = ": {$tanggal} ( Habis Kontrak ke {$sequence} )";
            } else {
                $Worksheet->Range("E25")->Value = ": -";
            }


            $Worksheet->Range("G9")->Value = $lastCode;
            $Worksheet->Range("G8")->Value = $hisCreated  
                ? Carbon::parse($hisCreated)->locale('id')->translatedFormat('j F Y')
                : '';
            
            $picpath = public_path("assets/images/approved.png");
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel, $offset = 0) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top - $offset; // Offset untuk menggeser gambar ke atas
                $pic->Left = $excel->Cells($row, $column)->Left;                
            }
            foreach ($dataAppr as $appr) {
                if ($appr->sequence == 2) {
                    if ($appr->approvalAction == 3) {
                        $Worksheet->Range("A53")->Value = $appr->apprname;
                        $Worksheet->Range("A54")->Value = $appr->apprtype;
                        addPictureToWorksheet($Worksheet, $picpath, 52, 1, 40, $excel, 25);
                    }
                }
                if ($appr->sequence == 3) {
                    if ($appr->approvalAction == 3) {
                        $Worksheet->Range("D53")->Value = $appr->apprname;
                        $Worksheet->Range("D54")->Value = $appr->apprtype;
                        addPictureToWorksheet($Worksheet, $picpath, 52, 4, 40, $excel, 25);
                    }
                }
                if ($appr->sequence == 4) {
                    if ($appr->approvalAction == 3) {
                        $Worksheet->Range("E53")->Value = $appr->apprname;
                        $Worksheet->Range("E54")->Value = $appr->apprtype;
                        addPictureToWorksheet($Worksheet, $picpath, 52, 5, 40, $excel, 25);
                    }
                }
                if ($appr->sequence == 5) {
                    if ($appr->approvalAction == 3) {
                        $Worksheet->Range("G53")->Value = $appr->apprname;
                        $Worksheet->Range("G54")->Value = $appr->apprtype;
                        addPictureToWorksheet($Worksheet, $picpath, 52, 7, 40, $excel, 25);
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
            // Update baris terakhir atau tertentu dari request_memorandum_detail
            DB::table('request_memorandum_detail')
                ->where('req_id', $id)
                ->orderByDesc('sequence')
                ->limit(1)
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