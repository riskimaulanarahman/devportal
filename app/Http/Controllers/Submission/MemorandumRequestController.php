<?php

namespace App\Http\Controllers\Submission;

use DB;
use COM;
use Log;
use App\Models\User;
use App\Models\Module;
use Carbon\Carbon;

use App\Models\Employee;
use App\Models\Attachment;
use App\Models\Useraccess;
use App\Mail\SubmissionMail;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\Submission\MemorandumDetail;
// use Illuminate\Support\Carbon;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\Memorandum;
use App\Mail\ReminderMail;
use function PHPUnit\Framework\fileExists;

class MemorandumRequestController extends Controller
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

            // $memos = DB::table('memoExp')->get();
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
                ->whereIn('employee_id', DB::table('memoExp')->pluck('id'))
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
                        m.dayToExp,
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
            //iki ketika data sudah banyak
            $data = $data->filter(function ($item) {
                return is_null($item->dayToExp) || 
                    (is_numeric($item->dayToExp) && $item->dayToExp < 60);
            })->values();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => "empty",
                    'message' => "Data tidak ditemukan, silakan tambahkan data baru",
                    'data' => []
                ])->setEncodingOptions(JSON_NUMERIC_CHECK);
            }
            
            // dd($data);  
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }


    public function reminderNotificationMessage($mode)
    {
        if ($mode == 'reminder') {
            $getReminderMemo = DB::table('memoExp')->get();
            $grouped = [];

            foreach ($getReminderMemo as $r) {
                if (is_numeric($r->dayToExp) && (int)$r->dayToExp < 60 && (int)$r->dayToExp >= 0) {

                    $getSubmissionData = DB::table('request_memorandum')
                        ->where('employee_id', $r->id)
                        ->first();                        

                    if ($getSubmissionData) {
                        $getCreator = DB::table('users')->where('id', $getSubmissionData->user_id)->first();

                        $getDetailData = DB::table('request_memorandum_detail')
                        ->where('req_id', $getSubmissionData->id)
                        ->first();

                        if ($getCreator && isset($getCreator->email)) {
                            $email = $getCreator->email;
                            $grouped[$email]['getCreator'] = $getCreator;
                            $grouped[$email]['submissions'][] = (object)[
                                // 'code_id'          => $getDetailData->code_id ?? '-',
                                'bu'      => $r->bu ?? '-',
                                'emp_name'      => $r->FullName ?? '-',
                                'dayToExp'      => $r->dayToExp ?? '-',
                                'contract_status'      => $r->contract_status ?? '-',
                                'retirement_date'      => $r->retirement_date ?? '-',
                                'sequence'      => $getDetailData->sequence ?? '-',
                                'startContract' => $getDetailData->startContract ?? '-',
                                'endContract'   => $getDetailData->endContract ?? '-',
                                'remarks'       => $getDetailData->remarks ?? '-',
                            ];
                        }
                    }
                }
            }

            foreach ($grouped as $email => $payload) {
                $mailData = [
                    "all" => 1,
                    "action_id" => 0,
                    "submission" => null, // supaya tidak trigger email lama
                    "submissions" => $payload['submissions'],
                    "email" => $email,
                    "fullname" => $payload['getCreator']->fullname ?? '-',
                    "message" => $this->mailMessage()['deadlineTaskReminder'],
                    "mailType" => "reminder",
                ];

                // dd($mailData);
                Mail::to($email)->send(new ReminderMail($mailData, $this->modulename, 1));
            }
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
            ->select('request_memorandum_detail.*')
            ->where('req_id', $id)
            ->orderBy('sequence')
            ->get();

        if ($contracts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "Kontrak kosong untuk req_id: $id"
            ]);
        }
        $lastDetail   = $contracts->last();
        $hisCreated   = $lastDetail->created_at ?? null;
        $lastRemarks  = $lastDetail->remarks ?? '-';
        $laststartcontract  = $lastDetail->startContract ?? '-';
        $lastsendcontract  = $lastDetail->endContract ?? '-';
        $lastCode     = $lastDetail->code_id ?? '-';        
        $lss = $lastDetail->sequence ?? '-';
        $today = Carbon::today();
        $oldContracts = [];
        $currentContracts = [];
        $newContracts = [];
        $currentContract = null; 
        foreach ($contracts as $contract) {
            $start = Carbon::parse($contract->startContract);
            $end   = Carbon::parse($contract->endContract);

            if ($end->lt($today)) {
                $oldContracts[] = $contract;
            } elseif ($start->lte($today) && $end->gte($today)) {
                $currentContracts[] = $contract;

                if (!$currentContract) {
                    $currentContract = $contract;
                }
            } elseif ($start->gt($today)) {
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
            $file = public_path("template/memo/template.xlsx");
            $bu = strtoupper(trim($data->bu));
            $statusRaw = $data->contract_status;
            $status = strtoupper(trim($statusRaw));

            $statusMap = [
                'CONTRACT' => 'KONTRAK',
                'PERMANENT' => 'PENSIUN'
            ];

            if (!array_key_exists($status, $statusMap)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown contract status: ' . ($statusRaw ?: 'NULL')
                ]);
            }
            $sheetName = $statusMap[$status];
            $Workbook = $excel->Workbooks->Open($file, false, true);
            $sheetNames = [];
            for ($i = 1; $i <= $Workbook->Worksheets->Count; $i++) {
                $sheetNames[] = $Workbook->Worksheets($i)->Name;
            }
            if (!in_array($sheetName, $sheetNames)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Sheet '{$sheetName}' tidak ditemukan dalam file template. Sheet yang tersedia: " . implode(', ', $sheetNames)
                ]);
            }
            $Worksheet = $Workbook->Worksheets($sheetName);
            $Worksheet->Activate();

            function addKopSuratStatis($Worksheet, $bu, $excel) {
                $picPath = '';

                if ($bu === 'AHL') {
                    $picPath = public_path('template/memo/kop/AHL.png');
                } elseif ($bu === 'IHM') {
                    $picPath = public_path('template/memo/kop/IHM.png');
                } elseif ($bu === 'NKL') {
                    $picPath = public_path('template/memo/kop/NKL.png');
                } elseif ($bu === 'KPS') {
                    $picPath = public_path('template/memo/kop/KPS.png');
                } elseif ($bu === 'KPSI') {
                    $picPath = public_path('template/memo/kop/KPSI.png');
                } elseif ($bu === 'GMS') {
                    $picPath = public_path('template/memo/kop/GMS.png');
                }

                if (!$picPath || !file_exists($picPath)) {
                    return;
                }

                $range = $Worksheet->Range("A1");

                if (!$range->MergeCells) {
                    return;
                }

                $mergedArea = $range->MergeArea;

                $targetWidth  = 24 * 28.35; // 24 cm
                $targetHeight = 3.2 * 28.35; // 3.2 cm

                $top  = $mergedArea->Top + ($mergedArea->Height - $targetHeight) / 2;
                $left = $mergedArea->Left + ($mergedArea->Width - $targetWidth) / 2;

                $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);

                $pic->LockAspectRatio = false;
                $pic->Top    = $top;
                $pic->Left   = $left;
                $pic->Width  = $targetWidth;
                $pic->Height = $targetHeight;
                $pic->Placement = 1; 
            }

            if (!file_exists($file)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File template tidak ditemukan: ' . $file
                ]);
            }
            addKopSuratStatis($Worksheet, $bu, $excel); 

            $Worksheet->Range("A53")->Value = $data->superiorName ?? '';
            $Worksheet->Range("B8")->Value = $data->deptheadName ?? '';
            $Worksheet->Range("D34")->Value = $lastRemarks ?? '';      
            $Worksheet->Range("C59")->Value = $data->Pendidikan ?? '-'; 
            $Worksheet->Range("C58")->Value = $data->DesignationName ?? ''; 
            $BirthOfDateRaw = $data->BirthOfDate ?? null;
            if ($BirthOfDateRaw) {
                $BirthOfDate = Carbon::parse($BirthOfDateRaw)->locale('id');
                $formattedBirthOfDate = "'" . $BirthOfDate->translatedFormat('j F Y'); 
                $Worksheet->Range("C56")->Value = $formattedBirthOfDate;
            } else {
                $Worksheet->Range("C56")->Value = '';
            }

            $Worksheet->Range("C57")->Value = $usia ?? 'N/A';
                
            $startDated = $laststartcontract ? Carbon::parse($laststartcontract) : null;
            $endDated   = $lastsendcontract ? Carbon::parse($lastsendcontract) : null;

            $Worksheet->Range("D31")->Value = ($startDated && $endDated)
                ? 'Dari ' . $startDated->locale('id')->translatedFormat('j F Y') .
                ' sampai dengan ' . $endDated->locale('id')->translatedFormat('j F Y')
                : ' - ';

            $startRow     = 56;
            $startNumber  = 5;
            $maxSequence  = $contracts->max('sequence');

            $historyContracts = $contracts->filter(fn($c) => $c->sequence < $maxSequence)->values();

            foreach ($historyContracts as $index => $contract) {
                $number         = $startNumber + $index;
                $contractLabel  = $contract->sequence;
                $histart        = $contract->startContract ? Carbon::parse($contract->startContract)->format('d/m/Y') : '-';
                $hisend         = $contract->endContract ? Carbon::parse($contract->endContract)->format('d/m/Y') : '-';
                $label          = "{$number}. Kontrak {$contractLabel} : {$histart} - {$hisend}";
                $row = $startRow + ($index % 4);
                $column = $index < 4 ? "D" : "E";
                $Worksheet->Range("{$column}{$row}")->Value = $label;
            }
            $Worksheet->Range("E13")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E16")->Value = ($data->FullName ?? ' ') . ' / ' . ($data->SAPID ?? ' '); 
            $Worksheet->Range("E19")->Value = $data->DesignationName ?? '-'; 
            $Worksheet->Range("E37")->Value = $data->deptheadName ?? ''; 
            $joinDateRaw = $data->JoinDate ?? null;
            if ($joinDateRaw) {
                $joinDate = Carbon::parse($joinDateRaw)->locale('id');
                $formattedJoinDate = "'" . $joinDate->translatedFormat('j F Y'); 
                $Worksheet->Range("E22")->Value = $formattedJoinDate;
            } else {
                $Worksheet->Range("E22")->Value = '';
            }        
            $formattedEndContract = '-';
            $lastminones = $contracts->max('sequence') - 1;
            $previousContract = $contracts->firstWhere('sequence', $lastminones);

            if ($status === 'PERMANENT') {
                $birthDate = $data->BirthOfDate ? Carbon::parse($data->BirthOfDate) : null;
                $pensiundate = $birthDate ? $birthDate->copy()->addYears(55) : null;

                if ($pensiundate) {
                    $formattedEndContract = $pensiundate->locale('id')->translatedFormat('j F Y');
                    $Worksheet->Range("E25")->Value = $formattedEndContract;
                } else {
                    $Worksheet->Range("E25")->Value = '-';
                }

            } elseif ($status === 'CONTRACT') {
                $lastendcontract = $previousContract && $previousContract->endContract
                    ? Carbon::parse($previousContract->endContract)
                    : null;

                if ($lastendcontract) {
                    $formattedEndContract = $lastendcontract->locale('id')->translatedFormat('j F Y');
                    $Worksheet->Range("E25")->Value = "{$formattedEndContract} ( Habis Kontrak ke {$lastminones} )";
                } else {
                    $Worksheet->Range("E25")->Value = '-';
                }
            } else {
                $Worksheet->Range("E25")->Value = '-';
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
                    if ($appr->approvalAction == 3 && $appr->bu != 'GMS' && $appr->bu != 'KPS') {
                        $Worksheet->Range("H53")->Value = $appr->apprname;
                        $Worksheet->Range("H54")->Value = $appr->apprtype;
                        addPictureToWorksheet($Worksheet, $picpath, 52, 7, 40, $excel, 25);
                    }
                }
            }

            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code_id); // Ubah pemisah menjadi underscore
            $todayDate = date('Ymd'); // Format tanggal hari ini: 20250717

            $fileName = "{$data->id}_Memorandum_{$code_sanitized}_{$todayDate}-{$lss}.pdf";
            $fileName = preg_replace("/[^a-z0-9_\-\.]/i", '', $fileName); // Bersihkan karakter tidak aman
            $filePath = public_path("template/memo/pdf/{$fileName}");

            if (file_exists($filePath)) {
                unlink($filePath); // Hapus jika file sudah ada
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
            $lastDetail = DB::table('request_memorandum_detail')
                ->where('req_id', $id)
                ->orderByDesc('sequence')
                ->first();
            DB::table('request_memorandum_detail')
                ->where('id', $lastDetail->id)
                ->update(['approveddoc' => $pathfilename]);
                if ($lastDetail->sequence == 1) {
                    $req = DB::table('request_memorandum')->where('id', $id)->first();

                    DB::table('employee.tbl_employee')
                        ->where('id', $req->employee_id)
                        ->where('contract_status', 'Permanent')
                        ->update(['contract_status' => 'Contract']);
                }
            $this->processcopy($pathfilename);
            return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-memorandum', $e->getMessage());
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}