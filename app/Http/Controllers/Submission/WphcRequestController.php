<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Submission\Wphc;

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
   public function logreportwphc()
    {
        try {
            // Ambil user yang sedang login
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "User belum login"
                ], 401);
            }

            // Ambil employee_id berdasarkan LoginName
            $employee = DB::table('employee.tbl_employee')
                ->where('LoginName', $user->username)
                ->select('id')
                ->first();

            if (!$employee) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data employee tidak ditemukan untuk user: {$user->username}"
                ], 404);
            }

            $employee_id = $employee->id;

            // Ambil semua detail WPHC milik employee login
            $rawData = DB::table('request_wphc_detail as rdw')
                ->join('request_wphc as rw', 'rdw.req_id', '=', 'rw.id')
                ->where('rw.employee_id', $employee_id)
                ->orderByDesc('rdw.work_date')
                ->select(
                    'rdw.id',
                    'rdw.req_id',
                    'rdw.work_date',
                    'rdw.remarks',
                    'rdw.text',
                    'rdw.created_at',
                    'rdw.updated_at',
                    'rw.requestStatus',
                    'rw.employee_id',
                    'rw.created_at as request_created_at'
                )
                ->get();

            $now = Carbon::now();

            // Hitung status aktif berdasarkan work_date + 3 bulan >= hari ini
            $data = $rawData->map(function ($item) use ($now) {
                $workDate = Carbon::parse($item->work_date);
                $aktifUntil = $workDate->copy()->addMonths(3);
                $isAktif = $aktifUntil->greaterThanOrEqualTo($now);

                $itemArray = collect($item)->toArray();

                return array_merge($itemArray, [
                    'status_wphc_aktif' => $isAktif ? 'aktif' : 'non-aktif',
                    'aktif_sampai_dengan' => $aktifUntil->format('d-m-Y'), // selalu tampil
                ]);
            });


            return response()->json([
                "status" => "show",
                "message" => "Data WPHC milik user login berhasil ditampilkan",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);

            $dataquery = $this->model->query();
            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id
                where l.ApprovalAction='1'
                and l.req_id = request_wphc.id and l.module_id = '".$module_id."' 
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
    ->where(function ($query) use ($subquery, $user_id) {
        $query->whereRaw("$subquery = 1")
            ->orWhere(function ($query) use ($user_id) {
                $query->where('request_wphc.user_id', '!=', $user_id)
                      ->whereIn('request_wphc.requestStatus', [1, 2, 3, 4]);
            })
            ->orWhere('request_wphc.user_id', $user_id);
    })
    ->orderBy(DB::raw($subquery), 'DESC')
    ->get();

                
                $data = $data->map(function ($item) {

                return $item;
            });
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

                // Tentukan category_id
                $level = (string) $employee->level_id;
                $requestData['category_id'] = in_array($level, ['1', '2', '3']) ? 30 :
                ($level === '4' ? 32 : null);
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
        // $requestWphcDetail = DB::table('request_wphc_detail')->select('work_date', 'remarks', 'text')->where('req_id', $id)->get();
        $dataDetail = DB::table('request_wphc_detail')->where('req_id', $id)->get();


        $data = $this->model->select(
            'request_wphc.*',
            'codes.code',
            'users.fullname',
            'emp.SAPID',
            'emp.loginName',
            'emp.FullName',
            'designation.DesignationName',
            'rwd.work_date',
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
    

        try {
            $excel = new COM("Excel.Application");
            $excel->Visible = false;

            $file = public_path("template/wphc/wphc.xlsx");

            if (!file_exists($file)) {
                throw new \Exception("File tidak ditemukan: " . $file);
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets(1);
            $Worksheet->Activate();

            $row = 37;
            foreach ($dataDetail as $detail) {
                $Worksheet->Range("B{$row}")->Value = (string) $detail->work_date;
                $Worksheet->Range("D{$row}")->Value = (string) $detail->remarks;
                $Worksheet->Range("K{$row}")->Value = (string) $detail->text;
                $row += 3;
            }

            // Isi Form Data
            $Worksheet->Range("F14")->Value = $data->SAPID;
            $Worksheet->Range("F16")->Value = $data->FullName;
            $Worksheet->Range("F18")->Value = $data->DesignationName;
            $Worksheet->Range("F20")->Value = $data->grade;
            $Worksheet->Range("F22")->Value = $data->bu;
            $Worksheet->Range("F24")->Value = $data->sector;
            $Worksheet->Range("F26")->Value = $data->employee_email;
            // $Worksheet->Range("F30")->Value = $data->code;
            $Worksheet->Range("M14")->Value = $data->superior_name;
            $Worksheet->Range("M16")->Value = $data->superior_email;
            $Worksheet->Range("B58")->Value = $data->FullName;
            // $Worksheet->Range("F58")->Value = $data->submit_date;
            $Worksheet->Range("F58")->Value = Carbon::parse($data->submit_date)->format('d/m/Y');
            $Worksheet->Range("F24")->Value = $data->Location;
            $Worksheet->Range("C64")->Value = $data->code;

            $picPath = public_path("assets/images/approved.png");

            if (file_exists($picPath)) {
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
                $approverMap = [];
                
                foreach ($dataAppr as $appr) {
                    if ($appr->approvalAction= 3) {
                        $approverMap[$appr->sequence] = $appr;
                    }
                }

                $row = 37;
                foreach ($dataDetail as $detail) {
                    $Worksheet->Range("B{$row}")->Value = (string) $detail->work_date;
                    $Worksheet->Range("D{$row}")->Value = (string) $detail->remarks;
                    $Worksheet->Range("K{$row}")->Value = (string) $detail->text;

                    if (isset($approverMap[3])) {
                        $Worksheet->Range("O" . ($row + 1))->Value = $approverMap[3]->apprname;
                        $Worksheet->Range("O" . ($row + 2))->Value = $approverMap[3]->approvalDate;
                        addPictureToWorksheet($Worksheet, $picPath, $row, 15, 12, $excel, true);
                    }

                    if (isset($approverMap[4])) {
                        $Worksheet->Range("P" . ($row + 1))->Value = $approverMap[4]->apprname;
                        $Worksheet->Range("P" . ($row + 2))->Value = $approverMap[4]->approvalDate;
                        addPictureToWorksheet($Worksheet, $picPath, $row, 16, 12, $excel, true);
                    }

                    if (isset($approverMap[5])) {
                        $Worksheet->Range("Q39")->Value = $approverMap[5]->apprname;
                        $Worksheet->Range("Q40")->Value = $approverMap[5]->approvalDate;
                        addPictureToWorksheet($Worksheet, $picPath, 38, 17, 12, $excel, true);
                    }
                    $row += 3;
                }

                addPictureToWorksheet($Worksheet, $picPath, 55, 2, 36, $excel);
                    

                if (isset($approverMap[2])) {
                        $Worksheet->Range("I58")->Value = $approverMap[2]->apprname;
                        $Worksheet->Range("M58")->Value = $approverMap[2]->approvalDate;
                        addPictureToWorksheet($Worksheet, $picPath, 55, 9, 36, $excel);
                    }

                if (isset($approverMap[6])) {
                        $Worksheet->Range("O58")->Value = $approverMap[6]->apprname;
                        $Worksheet->Range("Q58")->Value = $approverMap[6]->approvalDate;
                        addPictureToWorksheet($Worksheet, $picPath, 55, 15, 36, $excel);
                    }
                }
                // dd($approverMap);

            // Ekspor ke PDF
            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/wphc/pdf/' . $fileName);
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
            $pathfilename = 'public/template/wphc/pdf/' . $fileName;
            DB::table('request_wphc')
            ->where('id', $id)
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);
			return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-wphc', $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Error di " . $e->getFile() . " baris " . $e->getLine() . ": " . $e->getMessage()
            ]);
        }
    }
}