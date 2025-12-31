<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Submission\Wphc;


use App\Models\CategoryForm;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Module;
use App\Models\User;
use App\Models\Holiday;

use App\Mail\SubmissionMail;
use Carbon\Carbon;
use COM; 
use Log;

class WphcRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Wphc();
        $this->modulename = 'Wphc';
        $this->module = new Module();
        $this->user = new User();
    }

    public function scopeWithEmployeeInfo($query)
    {
        return $query->leftJoin('employee.tbl_employee as emp', 'request_wphc.employee_id', '=', 'emp.id')
                    ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id');
    }

    public function holiday()

    {
    $data = DB::table('tbl_holiday')
            ->get();

    return response()->json([
            "status" => "show",
            "message" => "Data WPHC milik user login berhasil ditampilkan",
            "data" => $data
        ]);
   }
//    public function logreportwphc()
//     {
//         try {
//             // Ambil user yang sedang login
//             $user = Auth::user();

//             if (!$user) {
//                 return response()->json([
//                     "status" => "error",
//                     "message" => "User belum login"
//                 ], 401);
//             }

//             // Ambil employee_id berdasarkan LoginName
//             $employee = DB::table('employee.tbl_employee')
//                 ->where('LoginName', $user->username)
//                 ->select('id')
//                 ->first();

//             if (!$employee) {
//                 return response()->json([
//                     "status" => "error",
//                     "message" => "Data employee tidak ditemukan untuk user: {$user->username}"
//                 ], 404);
//             }

//             $employee_id = $employee->id;

//             // Ambil semua detail WPHC milik employee login
//             $rawData = DB::table('request_wphc_detail as rdw')
//                 ->join('request_wphc as rw', 'rdw.req_id', '=', 'rw.id')
//                 ->where('rw.employee_id', $employee_id)
//                 ->where('rw.requestStatus', 3)
//                 ->orderByDesc('rdw.startDate')
//                 ->select(
//                     'rdw.id',
//                     'rdw.req_id',
//                     'rdw.startDate as work_date',
//                     'rdw.remarks',
//                     'rdw.text',
//                     'rdw.created_at',
//                     'rdw.updated_at',
//                     'rw.requestStatus',
//                     'rw.employee_id',
//                     'rw.created_at as request_created_at'
//                 )
//                 ->get();

//             $now = Carbon::now();

//             // Hitung status aktif berdasarkan work_date + 4 bulan >= hari ini
//             $data = $rawData->map(function ($item) use ($now) {
//                 $work_date = Carbon::parse($item->work_date);
//                 $aktifUntil = $work_date->copy()->addMonths(4);
//                 $isAktif = $aktifUntil->greaterThanOrEqualTo($now);

//                 $itemArray = collect($item)->toArray();

//                 return array_merge($itemArray, [
//                     'status_wphc_aktif' => $isAktif ? 'aktif' : 'non-aktif',
//                     'aktif_sampai_dengan' => $aktifUntil->format('d-m-Y'), // selalu tampil
//                 ]);
//             });


//             return response()->json([
//                 "status" => "show",
//                 "message" => "Data WPHC milik user login berhasil ditampilkan",
//                 "data" => $data
//             ]);

//         } catch (\Exception $e) {
//             return response()->json([
//                 "status" => "error",
//                 "message" => "Terjadi kesalahan: " . $e->getMessage()
//             ], 500);
//         }
//     }
    public function index(Request $request)
    {
        try {
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $user = auth()->user();
            $module_id = $this->getModuleId($this->modulename);

            $dataquery = $this->model->query();
            $subquery = "(select TOP 1 
                            CASE WHEN a.user_id='".$user_id."' then 1 else 0 end
                        from tbl_approverListReq l
                        left join tbl_approver a on l.approver_id=a.id
                        left join tbl_approvaltype r on a.approvaltype_id = r.id
                        where l.ApprovalAction='1'
                        and l.req_id = request_wphc.id 
                        and l.module_id = '".$module_id."' 
                        and request_wphc.requestStatus='1'
                        order by a.sequence)";
            
            // Subquery untuk lastApprovalDate
            $lastApprovalDate = "(SELECT TOP 1 l.approvalDate
                FROM tbl_approverListReq l
                WHERE l.req_id = request_wphc.id 
                AND l.module_id = '".$module_id."' 
                AND l.approvalDate IS NOT NULL 
                AND l.ApprovalAction != '1'
                ORDER BY l.approvalDate DESC)";

            // Subquery untuk nextApproverName
            $nextApproverName = "(SELECT TOP 1 e.FullName
                FROM tbl_approverListReq l
                JOIN tbl_approver a ON l.approver_id = a.id
                JOIN employee.tbl_employee e ON a.employee_id = e.id
                WHERE l.req_id = request_wphc.id 
                AND l.module_id = '".$module_id."' 
                AND l.ApprovalAction = '1'
                ORDER BY a.sequence ASC)";
            
            $data = $dataquery
                ->selectRaw("
                    request_wphc.*,
                    codes.code,
                    emp.FullName,
                    emp.SAPID,
                    designation.DesignationName,
                    CASE WHEN request_wphc.user_id = '$user_id' THEN 1 ELSE 0 END AS isMine,
                    $subquery AS isPendingOnMe,
                    $lastApprovalDate AS lastApprovalDate,
                    $nextApproverName AS nextApproverName
                ")
                ->leftJoin('codes', 'request_wphc.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee as emp', 'request_wphc.employee_id', '=', 'emp.id')
                ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id')
                ->with(['user', 'approverlist', 'wphc_detail'])
                ->where(function ($q) use ($user_id, $subquery, $user) {
                    $q->where('request_wphc.user_id', $user_id)   // isMine = 1
                    ->orWhereRaw("$subquery = 1");              // isPendingOnMe = 1

                    if ($user->isAdmin) {
                        $q->orWhere(function ($q2) {
                            $q2->whereIn('request_wphc.requestStatus', [1, 3]);  
                        });
                    }

                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->get();  

            //     $data = $data->map(function ($item) {

            //     return $item;
            // });
            // Map untuk menambahkan field work_dates (gabungan semua startDate dari detail)
            $data = $data->map(function ($item) {
                $dates = [];

                foreach ($item->wphc_detail ?? [] as $detail) {
                    // sesuaikan nama kolom di sini
                    $raw = $detail->startDate ?? $detail->work_date ?? null;

                    if (!empty($raw)) {
                        try {
                            $dates[] = \Carbon\Carbon::parse($raw)->format('d-m-Y');
                        } catch (\Throwable $ex) {
                            $dates[] = (string) $raw;
                        }
                    }
                }

                $item->work_dates = !empty($dates) ? implode(', ', $dates) : null;
                return $item;
            });


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
    
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->getAuth();
            $requestData = $request->all();

            // Ambil employee berdasarkan LoginName
            $employee = DB::table('employee.tbl_employee as emp')
                ->leftJoin('users as usr', 'emp.LoginName', '=', 'usr.username')
                ->where('usr.id', $user->id)
                ->select('emp.*')
                ->first();

            if ($employee) {
                $requestData['employee_id'] = $employee->id;
                $requestData['bu'] = $employee->companycode;
                $requestData['level'] = $employee->level_id;

                // Tentukan DeptHead ID
                $deptHead = DB::table('employee.tbl_employee')
                    ->whereRaw('LOWER(fullname) = ?', [strtolower($employee->deptheadName)])
                    ->select('id')
                    ->first();

                $requestData['DeptHead'] = $deptHead ? $deptHead->id : null;

                // Tentukan sector
                $requestData['sector'] = in_array($employee->companycode, ['IHM', 'AHL', 'KPSI', 'NKL']) ? 'HO' : $employee->companycode;

                // Ambil semua kategori WPHC (module_id = 92)
                $categories = CategoryForm::all()->keyBy('nameCategory');

                // Tentukan category_id berdasarkan level
                $level = (int) $employee->level_id;
                if (in_array($level, [1, 2, 3], true)) {
                    $requestData['category_id'] = $categories['Asst - Askep']->id ?? null;
                } elseif ($level === 4) {
                    $requestData['category_id'] = $categories['Manager - Up']->id ?? null;
                } else {
                    $requestData['category_id'] = null;
                }
            }

            $requestData['user_id'] = $user->id;

            $newData = $this->model->create($requestData);
            // dd($newData);
            $id = $newData->id;
            DB::commit();
            // Inject approval DeptHead jika tersedia
            if (in_array($employee->level_id, [2, 5]) && $requestData['DeptHead']) {
                $this->createApprDeptHead($requestData['DeptHead'], $this->modulename, $id);
            }
            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {

            $data = $this->model->select('request_wphc.*','codes.code')
            ->leftJoin('codes','request_wphc.code_id','codes.id')
            ->where('request_wphc.id',$id)
            ->with(['user'])
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename);
                $data->save();
            }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {

            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            // $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            if($request->Superior) {
                $this->createApprSuperior($request->Superior, $this->modulename, $id);
            }
            if($request->DeptHead) {
                $this->createApprDeptHead($request->DeptHead, $this->modulename, $id);
            }
            
            $data->update($requestData);
            //end save history perubahan

            if(isset($request->ticketStatus) && $data->requestStatus == 3) {
                $getSubmissionData = $data;

                $mailData = [
                    "id" => 30, // final approved
                    "action_id" => 5, // update id
                    "submission" => $getSubmissionData,
                    "email" => $this->getUserByid($getSubmissionData->user_id)->email, // kirim kepada creator
                    "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
                    "message" => $this->mailMessage()['newActivity'],
                    "remarks" => $request->ticketStatus
                ];
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
            }

            // Mengembalikan data dalam bentuk JSON dengan memberikan status, pesan dan data
            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);

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

    public function genPdfWphc(Request $request, $id) 
    {
        $dataAppr = DB::table('wphcApprover')->select('*')->where('id', $id)->get(); // Data approver
        $dataDetail = DB::table('request_wphc_detail')->where('req_id', $id)->get();


        $data = $this->model->select(
            'request_wphc.*',
            'codes.code',
            'users.fullname',
            'emp.SAPID',
            'emp.loginName',
            'emp.FullName',
            'designation.DesignationName',
            'rwd.startDate',
            'rwd.remarks',
            'rwd.text',
            'loc.Location',
            'usr.email',
            'sup.FullName as superior_name',
            'sup_usr.email as superior_email',
            'emp_usr.email as employee_email',
            'emp_usr.fullname as employee_name',
            'level.id as level',
            'alh_sub.fullname as submitter_name',
            'alh_sub.approvalDate as submit_date'
        )
        ->leftJoin('codes', 'request_wphc.code_id', '=', 'codes.id')
        ->leftJoin('users', 'request_wphc.user_id', '=', 'users.id')
        ->leftJoin('employee.tbl_employee as emp', 'request_wphc.employee_id', '=', 'emp.id')
        ->leftJoin('users as usr', 'emp.LoginName', '=', 'usr.username')
        ->leftJoin('request_wphc_detail as rwd', 'request_wphc.id', '=', 'rwd.req_id')
        ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id')
        ->leftJoin('employee.tbl_location as loc', 'emp.location_id', '=', 'loc.id')
        ->leftJoin('employee.tbl_employee as sup', 'request_wphc.Superior', '=', 'sup.id')
        ->leftJoin('users as emp_usr', 'emp.LoginName', '=', 'emp_usr.username')
        ->leftJoin('users as sup_usr', 'sup.LoginName', '=', 'sup_usr.username')
        ->leftJoin('employee.tbl_level as level', 'emp.level_id', '=', 'level.id')
        ->leftJoin(DB::raw("(
            SELECT req_id, fullname, approvalType, approvalDate
            FROM (
                SELECT *,
                    ROW_NUMBER() OVER (PARTITION BY req_id ORDER BY approvalDate DESC) AS rn
                FROM tbl_approverlistHistory
                WHERE approvalType = 'Submitted'
            ) AS filtered
            WHERE rn = 1
        ) as alh_sub"), 'alh_sub.req_id', '=', 'request_wphc.id')
        ->where('request_wphc.id', $id)
        ->first();

        if (!$data || !$dataAppr) {
            return response()->json(["status" => "error", "message" => "Data or dataappr not found"]);
        }

        // dd($data, $dataAppr, $dataDetail);
    
    $picPath = public_path("assets/images/approved.png");
    try {
        $excel = new COM("Excel.Application");
        $excel->Visible = false;

        $file = public_path("template/wphc/wphc.xlsx");
        if (!file_exists($file)) {
            throw new \Exception("File tidak ditemukan: " . $file);
        }

        $Workbook  = $excel->Workbooks->Open($file, false, true);
        $Worksheet = $Workbook->Worksheets(1);
        $Worksheet->Activate();

        // ===== Helpers =====
        function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel, $center = false)
        {
            $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);
            $pic->Height = $height;
            $cell = $excel->Cells($row, $column);
            $pic->Top  = $cell->Top;
            $pic->Left = $cell->Left;

            if ($center) {
                $pic->Left = $cell->Left + (($cell->Width - $pic->Width) / 2);
                $pic->Top  = $cell->Top  + (($cell->Height - $pic->Height) / 2);
            }
        }

        // New helper: place picture centered inside a merged range
        // helper: place picture centered inside a merged range (no border)
        function addPictureToMergedRange($Worksheet, $picPath, $rangeAddress, $height, $excel, $offsetTop = 2)
        {
            $range = $Worksheet->Range($rangeAddress);
            $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);
            $pic->Height = $height;

            $picLeft = $range->Left + (($range->Width - $pic->Width) / 2);
            $picTop  = $range->Top  + $offsetTop;

            $pic->Left = $picLeft;
            $pic->Top  = $picTop;
        }


        function applyMergeBordersDetail($Worksheet, $row)
        {
            // Date (B)
            $rangeDate = $Worksheet->Range("B{$row}:B" . ($row+2));
            $rangeDate->Merge();
            foreach ([7,8,9,10] as $borderIndex) {
                $rangeDate->Borders($borderIndex)->LineStyle = 1;
                $rangeDate->Borders($borderIndex)->Weight    = 2;
            }
            $rangeDate->Font->Name = "Arial";
            $rangeDate->Font->Size = 12;
            $rangeDate->HorizontalAlignment = -4131;
            $rangeDate->VerticalAlignment   = -4108;
            $rangeDate->WrapText = true;

            // Objectives (C–H)
            $rangeObj = $Worksheet->Range("C{$row}:H" . ($row+2));
            $rangeObj->Merge();
            foreach ([7,8,9,10] as $borderIndex) {
                $rangeObj->Borders($borderIndex)->LineStyle = 1;
                $rangeObj->Borders($borderIndex)->Weight    = 2;
            }
            $rangeObj->Font->Name = "Arial";
            $rangeObj->Font->Size = 12;
            $rangeObj->HorizontalAlignment = -4131;
            $rangeObj->VerticalAlignment   = -4108;
            $rangeObj->WrapText = true;

            // Achievement (I–L)
            $rangeAch = $Worksheet->Range("I{$row}:L" . ($row+2));
            $rangeAch->Merge();
            foreach ([7,8,9,10] as $borderIndex) {
                $rangeAch->Borders($borderIndex)->LineStyle = 1;
                $rangeAch->Borders($borderIndex)->Weight    = 2;
            }
            $rangeAch->Font->Name = "Arial";
            $rangeAch->Font->Size = 12;
            $rangeAch->HorizontalAlignment = -4131;
            $rangeAch->VerticalAlignment   = -4108;
            $rangeAch->WrapText = true;
        }

        function renderNoteSection($Worksheet, $startRow)
        {
            $notes = [
                "Note:",
                "- The coverage days listed will be forfeited if it is not claimed within the validity period as per SOP.",
                "- Level of approval:",
                ":: C1-D1 National Staff must be approved by Dept. Head & BU Head.",
                ":: D2 and above National Staff and all level International Staff must be approved by BU Head & MD of KF."
            ];

            foreach ($notes as $i => $text) {
                $row   = $startRow + $i;
                $range = $Worksheet->Range("B{$row}:Q{$row}");
                $range->Merge();
                $range->WrapText = true;
                $range->Font->Size = 12;
                $range->Font->Name = "Arial";
                $range->Font->Bold = false;
                $range->HorizontalAlignment = -4131;
                $range->VerticalAlignment   = -4108;
                $range->Value = $text;
            }

            return $startRow + count($notes);
        }

        function renderDetailBlock($Worksheet, $detail, $row, $approverMap, $picPath, $excel)
            {
                // Merge + border untuk Approver kolom M/N/O (3 baris)
                foreach (['M','N','O'] as $col) {
                    $mergedAddr = "{$col}{$row}:{$col}" . ($row+2);
                    $range = $Worksheet->Range($mergedAddr);
                    $range->Merge();
                    foreach ([7,8,9,10] as $borderIndex) {
                        $range->Borders($borderIndex)->LineStyle = 1;
                        $range->Borders($borderIndex)->Weight    = 2;
                    }
                    $range->Font->Name = "Arial";
                    $range->Font->Size = 12;
                    $range->WrapText = true;
                    // default center for merged area
                    $range->HorizontalAlignment = -4108; // xlCenter
                    $range->VerticalAlignment   = -4108; // xlCenter
                }

                // Merge + border untuk Date, Objectives, Achievement
                applyMergeBordersDetail($Worksheet, $row);

                // Isi data detail
                $Worksheet->Range("B{$row}")->Value = date('d/m/Y', strtotime($detail->startDate));
                $Worksheet->Range("C{$row}")->Value = $detail->remarks ?: '-';
                $Worksheet->Range("I{$row}")->Value = $detail->text ?: '-';

                // mapping kolom -> approver index
                $map = ['M' => 2, 'N' => 4, 'O' => 5];

                foreach ($map as $col => $idx) {
                    $mergedAddr = "{$col}{$row}:{$col}" . ($row+2);
                    $range = $Worksheet->Range($mergedAddr);

                    if (!empty($approverMap[$idx])) {
                        $approver = $approverMap[$idx];

                        // gambar: gunakan helper jika ada, ukuran kecil agar tidak menimpa teks
                        if (file_exists($picPath)) {
                            // jika kamu punya addPictureToMergedRange, ini akan men-center gambar di merged range
                            // tinggi 26 agar aman; offset terakhir di helper (jika ada) bisa disesuaikan
                            addPictureToMergedRange($Worksheet, $picPath, $mergedAddr, 26, $excel, 2);
                        }

                        // siapkan nama dan tanggal (cek validitas tanggal sebelum parse)
                        $name = $approver->apprname ?: '';
                        $date = '';
                        if (!empty($approver->approvalDate) && strtotime($approver->approvalDate) !== false) {
                            $date = Carbon::parse($approver->approvalDate)->format('d/m/Y');
                        }

                        // tulis nama + newline + tanggal ke top-left cell dari merged range
                        $value = $name . ($date !== '' ? "\n" . $date : '');
                        $cell = $Worksheet->Range("{$col}{$row}");
                        $cell->Value = $value;
                        $cell->WrapText = true;
                        // tampilkan teks di bagian bawah merged area agar terlihat sebagai baris ke-2 & ke-3
                        $cell->HorizontalAlignment = -4108; // center
                        $cell->VerticalAlignment   = -4107; // bottom
                        $cell->Font->Name = "Arial";
                        $cell->Font->Size = 12;
                        $cell->Font->Bold = false;
                    } else {
                        // jika tidak ada approver: jangan Clear() — biarkan merged range & border tetap tampil
                        $range->Value = "";            // kosongkan isi tapi jangan hapus range
                        $range->WrapText = true;
                        $range->HorizontalAlignment = -4108;
                        $range->VerticalAlignment   = -4108;
                        // re-apply border untuk memastikan tetap terlihat
                        foreach ([7,8,9,10] as $b) { $range->Borders($b)->LineStyle = 1; $range->Borders($b)->Weight = 2; }
                    }
                }

                return $row + 3; // lanjut ke blok berikutnya
            }


        // ===== Header fill =====
        $Worksheet->Range("D14")->Value = $data->SAPID;
        $Worksheet->Range("D16")->Value = $data->FullName;
        $Worksheet->Range("D18")->Value = $data->DesignationName;
        $Worksheet->Range("D20")->Value = $data->grade;
        $Worksheet->Range("D22")->Value = $data->bu;
        $Worksheet->Range("D24")->Value = $data->sector;
        $Worksheet->Range("D26")->Value = $data->employee_email;
        $Worksheet->Range("K14")->Value = $data->superior_name;
        $Worksheet->Range("K16")->Value = $data->superior_email;
        $Worksheet->Range("D24")->Value = $data->Location;

        // ===== Approver mapping =====
        $approverMap = [];
        foreach ($dataAppr as $appr) {
            if ((int)$appr->approvalAction === 3) {
                $approverMap[(int)$appr->sequence] = $appr;
            }
        }

        // ===== Detail blocks =====
        $row = 37;
        foreach ($dataDetail as $detail) {
            $row = renderDetailBlock($Worksheet, $detail, $row, $approverMap, $picPath, $excel);
        }

        // ===== Notes =====
        $noteStartRow = $row + 1;
        $row = renderNoteSection($Worksheet, $noteStartRow);

        // ===== Applied/Assigned/Acknowledged =====
        $signatureBlockRow = $row + 2;
        $Worksheet->Range("B{$signatureBlockRow}")->Value = "Applied By:";
        $Worksheet->Range("G{$signatureBlockRow}")->Value = "Assigned By:";
        $Worksheet->Range("M{$signatureBlockRow}")->Value = "Acknowledged By:";
        foreach (['B','G','M'] as $col) {
            $range = $Worksheet->Range("{$col}{$signatureBlockRow}:{$col}" . ($signatureBlockRow+1));
            $range->Merge();
            // format label atas
            $range->Font->Name = "Arial";
            $range->Font->Size = 12;
            $range->Font->Bold = true;
            $range->HorizontalAlignment = -4131; // left
            $range->VerticalAlignment   = -4108;
        }

        // ===== Signature Blocks (4 baris kosong + name + role) =====
        $stampStartRow = $signatureBlockRow + 2;
        $stampEndRow   = $stampStartRow + 3;
        $nameRow       = $stampEndRow + 1;
        $roleRow       = $nameRow + 1;

        // common params untuk gambar
        $imgHeight = 36;
        $offsetTop = 2;   // jarak dari atas merged area
        $leftPadding = 6; // jarak dari kiri merged area (atur supaya rata kiri)

        // ---- Applied By block (B) ----
        $rangeB = $Worksheet->Range("B{$stampStartRow}:B{$stampEndRow}");
        $rangeB->Merge();
        // format area gambar (tidak bold)
        $rangeB->Font->Name = "Arial";
        $rangeB->Font->Size = 12;
        $rangeB->Font->Bold = false;
        $rangeB->HorizontalAlignment = -4131;
        $rangeB->VerticalAlignment = -4108;

        if (file_exists($picPath)) {
            $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);
            $pic->Height = $imgHeight;
            $pic->Left = $rangeB->Left + $leftPadding;
            $pic->Top  = $rangeB->Top  + $offsetTop;
        }
        // nama & role (regular)
        $cellBName = $Worksheet->Range("B{$nameRow}");
        $cellBName->Value = $data->FullName;
        $cellBName->Font->Name = "Arial";
        $cellBName->Font->Size = 12;
        $cellBName->Font->Bold = false;
        $cellBName->HorizontalAlignment = -4131;
        $cellBName->VerticalAlignment = -4107;

        $cellBRole = $Worksheet->Range("B{$roleRow}");
        $cellBRole->Value = "Employee";
        $cellBRole->Font->Name = "Arial";
        $cellBRole->Font->Size = 12;
        $cellBRole->Font->Bold = false;
        $cellBRole->HorizontalAlignment = -4131;
        $cellBRole->VerticalAlignment = -4108;

        // ---- Assigned By block (G) ----
        $rangeG = $Worksheet->Range("G{$stampStartRow}:G{$stampEndRow}");
        $rangeG->Merge();
        $rangeG->Font->Name = "Arial";
        $rangeG->Font->Size = 12;
        $rangeG->Font->Bold = false;
        $rangeG->HorizontalAlignment = -4131;
        $rangeG->VerticalAlignment = -4108;

        if (isset($approverMap[1])) {
            if (file_exists($picPath)) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);
                $pic->Height = $imgHeight;
                $pic->Left = $rangeG->Left + $leftPadding;
                $pic->Top  = $rangeG->Top  + $offsetTop;
            }
            $cellGName = $Worksheet->Range("G{$nameRow}");
            $cellGName->Value = $approverMap[1]->apprname;
            $cellGName->Font->Name = "Arial";
            $cellGName->Font->Size = 12;
            $cellGName->Font->Bold = false;
            $cellGName->HorizontalAlignment = -4131;
            $cellGName->VerticalAlignment = -4107;

            $cellGRole = $Worksheet->Range("G{$roleRow}");
            $cellGRole->Value = "Superior";
            $cellGRole->Font->Name = "Arial";
            $cellGRole->Font->Size = 12;
            $cellGRole->Font->Bold = false;
            $cellGRole->HorizontalAlignment = -4131;
            $cellGRole->VerticalAlignment = -4108;
        }

        // ---- Acknowledged By block (M) ----
        $rangeM = $Worksheet->Range("M{$stampStartRow}:M{$stampEndRow}");
        $rangeM->Merge();
        $rangeM->Font->Name = "Arial";
        $rangeM->Font->Size = 12;
        $rangeM->Font->Bold = false;
        $rangeM->HorizontalAlignment = -4131;
        $rangeM->VerticalAlignment = -4108;

        if (isset($approverMap[6])) {
            if (file_exists($picPath)) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, false, true, 0, 0, -1, -1);
                $pic->Height = $imgHeight;
                $pic->Left = $rangeM->Left + $leftPadding;
                $pic->Top  = $rangeM->Top  + $offsetTop;
            }
            $cellMName = $Worksheet->Range("M{$nameRow}");
            $cellMName->Value = $approverMap[6]->apprname;
            $cellMName->Font->Name = "Arial";
            $cellMName->Font->Size = 12;
            $cellMName->Font->Bold = false;
            $cellMName->HorizontalAlignment = -4131;
            $cellMName->VerticalAlignment = -4107;

            $cellMRole = $Worksheet->Range("M{$roleRow}");
            $cellMRole->Value = "BG HR";
            $cellMRole->Font->Name = "Arial";
            $cellMRole->Font->Size = 12;
            $cellMRole->Font->Bold = false;
            $cellMRole->HorizontalAlignment = -4131;
            $cellMRole->VerticalAlignment = -4108;
        }

        // ===== Isi approval date tepat di atas label "Date" =====
        // Employee submit date (di kolom D, sejajar dengan nama di B)
        $cellDateEmp = $Worksheet->Range("D{$nameRow}");
        $cellDateEmp->Value = !empty($data->submit_date) ? Carbon::parse($data->submit_date)->format('d/m/Y') : '-';
        $cellDateEmp->Font->Name = "Arial";
        $cellDateEmp->Font->Size = 12;
        $cellDateEmp->Font->Bold = false;
        $cellDateEmp->HorizontalAlignment = -4131;
        $cellDateEmp->VerticalAlignment   = -4108;

        // Assigned By approval date (di kolom K, sejajar dengan nama di G)
        if (isset($approverMap[2])) {
            $cellDateAssigned = $Worksheet->Range("K{$nameRow}");
            $cellDateAssigned->Value = !empty($approverMap[2]->approvalDate) ? Carbon::parse($approverMap[2]->approvalDate)->format('d/m/Y') : '-';
            $cellDateAssigned->Font->Name = "Arial";
            $cellDateAssigned->Font->Size = 12;
            $cellDateAssigned->Font->Bold = false;
            $cellDateAssigned->HorizontalAlignment = -4131;
            $cellDateAssigned->VerticalAlignment   = -4108;
        }

        // Acknowledged By approval date (di kolom O, sejajar dengan nama di M)
        if (isset($approverMap[6])) {
            $cellDateAssigned = $Worksheet->Range("O{$nameRow}");
            $cellDateAssigned->Value = !empty($approverMap[2]->approvalDate) ? Carbon::parse($approverMap[2]->approvalDate)->format('d/m/Y') : '-';
            $cellDateAssigned->Font->Name = "Arial";
            $cellDateAssigned->Font->Size = 12;
            $cellDateAssigned->Font->Bold = false;
            $cellDateAssigned->HorizontalAlignment = -4131;
            $cellDateAssigned->VerticalAlignment   = -4108;
        }

        // ===== Label Employee / Date / Superior / Date / BG HR / Date =====
        $labelRow = $nameRow + 1;
        $Worksheet->Range("B{$labelRow}")->Value = "Employee";
        $Worksheet->Range("D{$labelRow}")->Value = "Date";
        $Worksheet->Range("G{$labelRow}")->Value = "Superior";
        $Worksheet->Range("K{$labelRow}")->Value = "Date";
        $Worksheet->Range("M{$labelRow}")->Value = "BG HR";
        $Worksheet->Range("O{$labelRow}")->Value = "Date";

        foreach (['B','D','G','K','M','O'] as $col) {
            $cell = $Worksheet->Range("{$col}{$labelRow}");
            $cell->Font->Name = "Arial";
            $cell->Font->Size = 12;
            $cell->Font->Bold = true; // label bawah tetap bold
            $cell->HorizontalAlignment = -4131; // left
            $cell->VerticalAlignment = -4108;
        }


        // ===== Export to PDF =====
        $xlTypePDF = 0;
        $xlQualityStandard = 0;

        $code_sanitized = str_replace('/', '_', $data->code);
        $fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
        $fileName = preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);

        $filePath = public_path('template/wphc/pdf/' . $fileName);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $Worksheet->ExportAsFixedFormat($xlTypePDF, $filePath, $xlQualityStandard);

        // ===== Cleanup =====
        $excel->CutCopyMode = false;
        $Workbook->Close(false);
        unset($Worksheet);
        unset($Workbook);
        $excel->Workbooks->Close();
        $excel->Quit();
        unset($excel);

        // ===== Persist path & post-process =====
        $pathfilename = 'public/template/wphc/pdf/' . $fileName;
        DB::table('request_wphc')->where('id', $id)->update(['approveddoc' => $pathfilename]);
        $this->processcopy($pathfilename);

        return $pathfilename;

    } catch (\Exception $e) {
        $this->logerror($request->ip(), $request->url(), 'gen-pdf-wphc', $e->getMessage());
        return response()->json([
            "status"  => "error",
            "message" => "Error di " . $e->getFile() . " baris " . $e->getLine() . ": " . $e->getMessage()
        ]);
    }
    }
}