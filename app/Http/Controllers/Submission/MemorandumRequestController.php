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

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $data = $this->model->findOrFail($id);

    //         if(isset($request->ticketStatus) && $data->requestStatus == 3) {
    //             $getSubmissionData = $this->model->findOrFail($id);

    //             $mailData = [
    //                 "id" => 30, // final approved
    //                 "action_id" => 5, // update id
    //                 "submission" => $getSubmissionData,
    //                 "email" => $this->getUserByid($getSubmissionData->user_id)->email, // kirim kepada creator
    //                 "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
    //                 "message" => $this->mailMessage()['newActivity'],                    
    //                 "emp_name" => $request->emp_name (request_memorandum.employee_id = memoExp.id get FullName as emp_name),
    //                 "startContract" => $request->startContract (request_memorandum.id = request_memorandum_detail.req_id get startContract),
    //                 "endContract" => $request->endContract (request_memorandum.id = request_memorandum_detail.req_id get endContract),
    //                 "remarks" => $request->remarks (request_memorandum.id = request_memorandum_detail.req_id get endContract)
    //             ];
    //             Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Additional approver berhasil ditambahkan.'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->model->findOrFail($id);

            if (isset($request->ticketStatus) && $data->requestStatus == 3) {
                $getSubmissionData = $this->model->findOrFail($id);
                $user = $this->getUserByid($getSubmissionData->user_id);

                // Ambil semua detail kontrak untuk RM ini
                $details = DB::table('request_memorandum_detail')
                    ->where('req_id', $getSubmissionData->id)
                    ->orderBy('sequence')
                    ->get();

                // Ambil nama karyawan dari memoExp
                $employee = DB::table('memoExp')
                    ->where('id', $getSubmissionData->employee_id)
                    ->first();

                // Susun daftar periode kontrak
                $contractPeriods = $details->map(function ($detail, $index) {
                    $start = $detail->startContract ? Carbon::parse($detail->startContract)->format('d-m-Y') : '-';
                    $end   = $detail->endContract ? Carbon::parse($detail->endContract)->format('d-m-Y') : '-';
                    return ". {$start} s.d {$end}";
                })->toArray();

                // Ambil remarks terakhir
                $lastRemarks = $details->last()->remarks ?? '-';

                $mailData = [
                    'id'              => 30,
                    'action_id'       => 5,
                    'submission'      => $getSubmissionData,
                    'email'           => $user->email ?? null,
                    'fullname'        => $user->fullname ?? '-',
                    'message'         => $this->mailMessage()['newActivity'],
                    'emp_name'        => $employee->FullName ?? '-',
                    'contractPeriods' => $contractPeriods,
                    'komentar'         => $lastRemarks,
                ];

                Mail::to($mailData['email'])->send(new SubmissionMail($mailData, $this->modulename, 1));
            }

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

        // Ambil semua kontrak berdasarkan req_id
        $contracts = DB::table('request_memorandum_detail')
            ->leftJoin('codes', 'request_memorandum_detail.code_id', '=', 'codes.id')
            ->select('request_memorandum_detail.*', 'codes.code')
            ->where('req_id', $id)
            ->orderBy('sequence')
            ->get();

        if ($contracts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "Kontrak kosong untuk req_id: $id"
            ]);
        }

        // Ambil informasi dari baris terakhir
        $lastDetail   = $contracts->last();
        $hisCreated   = $lastDetail->created_at ?? null;
        $lastRemarks  = $lastDetail->remarks ?? '-';
        $lastCode     = $lastDetail->code ?? '-';
        $lastcontract = $lastDetail->endContract ?? '-';
        $lss = $lastDetail->sequence ?? '-';

        // Ambil sequence dari baris saat ini
        $currentId    = $data->id ?? null;
        $currentDetail = $contracts->firstWhere('id', $currentId);

        $today = Carbon::today();
        $oldContracts = [];
        $currentContracts = [];
        $newContracts = [];
        $currentContract = null; // Ini yang akan kita gunakan untuk ambil sequence aktif

        foreach ($contracts as $contract) {
            $start = Carbon::parse($contract->startContract);
            $end   = Carbon::parse($contract->endContract);

            if ($end->lt($today)) {
                $oldContracts[] = $contract;
            } elseif ($start->lte($today) && $end->gte($today)) {
                $currentContracts[] = $contract;

                // Simpan kontrak aktif pertama
                if (!$currentContract) {
                    $currentContract = $contract;
                }
            } elseif ($start->gt($today)) {
                $newContracts[] = $contract;
            }
        }
        // Tentukan kontrak aktif dan kontrak baru
        $currentEndContract = null;
        if (strtoupper($data->contract_status) === 'PERMANENT') {
            $birthDate = $data->BirthOfDate ? Carbon::parse($data->BirthOfDate) : null;
            $currentEndContract = $birthDate ? $birthDate->copy()->addYears(55) : null;
        } elseif ($currentContract) {
            $currentEndContract = Carbon::parse($currentContract->endContract);
        }
        $sequence = $currentContract->sequence ?? '-';
        $currentEndContract   = null;
        $newStartContract     = null;
        $newEndContract       = null;
        $sequence = $currentContract->sequence ?? '-';
        if (strtoupper($data->contract_status) === "PERMANENT") {
            $birthDate = $data->BirthOfDate ? Carbon::parse($data->BirthOfDate) : null;
            $currentEndContract = $birthDate ? $birthDate->copy()->addYears(55) : null;
        } else {
            foreach ($currentContracts as $contract) {
                $currentStartContract = Carbon::parse($contract->startContract);
                $currentEndContract   = Carbon::parse($contract->endContract);
                break; // Ambil kontrak aktif pertama
            }

            foreach ($newContracts as $contract) {
                $newStartContract = Carbon::parse($contract->startContract);
                $newEndContract   = Carbon::parse($contract->endContract);
                break; // Ambil kontrak baru pertama
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

            // $file = ""; 
            $file = public_path("template/memo/template.xlsx");

            $bu = strtoupper(trim($data->bu));
            $status = strtoupper(trim($data->contract_status));

            $statusMap = [
                'CONTRACT' => 'KONTRAK',
                'PERMANENT' => 'PENSIUN'
            ];

            if (!array_key_exists($status, $statusMap)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown contract status: ' . ($data->contract_status ?: 'NULL')
                ]);
            }

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
                return; // Jika tidak ditemukan, abaikan
            }

            // Ambil range A1
            $range = $Worksheet->Range("A1");

            // Pastikan A1 sudah di-merge
            if (!$range->MergeCells) {
                return;
            }

            // Ambil area hasil merge
            $mergedArea = $range->MergeArea;

            // Ukuran target dalam cm → konversi ke points (1 cm = 28.35 pt)
            $targetWidth  = 24 * 28.35; // 24 cm
            $targetHeight = 3.2 * 28.35; // 3.2 cm

            // Hitung posisi agar gambar berada di tengah area merge
            $top  = $mergedArea->Top + ($mergedArea->Height - $targetHeight) / 2;
            $left = $mergedArea->Left + ($mergedArea->Width - $targetWidth) / 2;

            // Sisipkan gambar
            $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);

            // Paksa ukuran dan posisi
            $pic->LockAspectRatio = false;
            $pic->Top    = $top;
            $pic->Left   = $left;
            $pic->Width  = $targetWidth;
            $pic->Height = $targetHeight;

            // Opsional: kunci gambar agar ikut sel
            $pic->Placement = 1; // xlMoveAndSize
        }


            $sheetName = $statusMap[$status];

            if (!file_exists($file)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File template tidak ditemukan: ' . $file
                ]);
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets($sheetName);
            $Worksheet->Activate();

            addKopSuratStatis($Worksheet, $bu, $excel); 

            // Contoh pengisian data
            $Worksheet->Range("A53")->Value = $data->superiorName ?? '';
            $Worksheet->Range("B8")->Value = $data->deptheadName ?? '';
            // $Worksheet->Range("D34")->Value = $lastRemarks;      
            $Worksheet->Range("D34")->Value = ": " . ($lastRemarks ?? '-');      
            $Worksheet->Range("C59")->Value = $data->Pendidikan ?? '-'; 
            $Worksheet->Range("C58")->Value = $data->DesignationName ?? ''; 
            // $Worksheet->Range("C56")->Value = $data->BirthOfDate 
                // ? Carbon::parse($data->BirthOfDate)->locale('id')->translatedFormat('j F Y')
                // : '';

            $BirthOfDateRaw = $data->BirthOfDate ?? null;
            if ($BirthOfDateRaw) {
                $BirthOfDate = Carbon::parse($BirthOfDateRaw)->locale('id');
                $formattedBirthOfDate = "'" . $BirthOfDate->translatedFormat('j F Y'); // tanda kutip satu untuk paksa teks
                $Worksheet->Range("C56")->Value = $formattedBirthOfDate;
            } else {
                $Worksheet->Range("C56")->Value = '';
            }

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
            $startNumber = 5;

            foreach ($filteredContracts as $index => $contract) {
                $number = $startNumber + $index;

                // Format tanggal kontrak
                $startDate = $contract->startContract ? Carbon::parse($contract->startContract)->format('d/m/Y') : '-';
                $endDate   = $contract->endContract ? Carbon::parse($contract->endContract)->format('d/m/Y') : '-';
                $period    = "{$startDate} - {$endDate}";

                // Label + periode
                $label = "{$number}. Kontrak " . ($index + 1) . " : {$period}";

                // Tentukan kolom dan baris
                if ($number <= 8) {
                    $row = $startRow + ($number - 5);
                    $Worksheet->Range("D{$row}")->Value = $label;
                } else {
                    $row = $startRow + ($number - 9);
                    $Worksheet->Range("E{$row}")->Value = $label;
                }
            }
        }
            $Worksheet->Range("E13")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E16")->Value = ($data->FullName ?? ' ') . ' / ' . ($data->SAPID ?? ' '); 
            $Worksheet->Range("E19")->Value = $data->DesignationName ?? '-'; 
            $Worksheet->Range("E37")->Value = $data->deptheadName ?? ''; 
            $joinDateRaw = $data->JoinDate ?? null;
            if ($joinDateRaw) {
                $joinDate = Carbon::parse($joinDateRaw)->locale('id');
                $formattedJoinDate = "'" . $joinDate->translatedFormat('j F Y'); // tanda kutip satu untuk paksa teks
                $Worksheet->Range("E22")->Value = $formattedJoinDate;
            } else {
                $Worksheet->Range("E22")->Value = '';
            }

            // $lastcontractRaw = $lastcontract ?? null;
            // if ($lastcontractRaw) {
            //     $lastcontract = Carbon::parse($lastcontractRaw)
            //     ->locale('id');
            //     $formattedlastcontract = "'" . $lastcontract
            //     ->translatedFormat('j F Y'); // tanda kutip satu untuk paksa teks
            //     $Worksheet->Range("E25")->Value = $formattedlastcontract;
            // } else {
            //     $Worksheet->Range("E25")->Value = '';
            // }

            $endContractRaw = $lastDetail->endContract ?? null;
            if ($endContractRaw) {
                $formattedEndContract = "'" . Carbon::parse($endContractRaw)
                    ->locale('id')
                    ->translatedFormat('j F Y');

                $Worksheet->Range("E25")->Value = $formattedEndContract;
            } else {
                $Worksheet->Range("E25")->Value = '';
            }
            $Worksheet->Range("E25")->Value = ": {$formattedEndContract} ( Habis Kontrak ke {$lss} )";

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
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") .'-'. $lss. '.pdf';
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
            $lastDetail = DB::table('request_memorandum_detail')
                ->where('req_id', $id)
                ->orderByDesc('sequence')
                ->first();

            DB::table('request_memorandum_detail')
                ->where('id', $lastDetail->id)
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