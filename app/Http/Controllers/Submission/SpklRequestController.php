<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Submission\Spkl;

use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Module;
use App\Models\User;

use App\Mail\SubmissionMail;
use Carbon\Carbon;
use COM;

class SpklRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Spkl();
        $this->modulename = 'Spkl';
        $this->module = new Module();
        $this->user = new User();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);

            $dataquery = $this->model->query();

            // Subquery: apakah pending di user ini
            $subqueryPending = "(SELECT TOP 1 
                CASE WHEN a.user_id = '$user_id' THEN 1 ELSE 0 END
                FROM tbl_approverListReq l
                LEFT JOIN tbl_approver a ON l.approver_id = a.id
                LEFT JOIN tbl_approvaltype r ON a.approvaltype_id = r.id
                WHERE l.ApprovalAction = '1'
                AND l.req_id = request_spkl.id
                AND l.module_id = '$module_id'
                AND request_spkl.requestStatus = '1'
                ORDER BY a.sequence)";

            // Subquery: last approval date
            $subqueryLastApproval = "(SELECT TOP 1 l.approvalDate
                FROM tbl_approverListReq l
                WHERE l.req_id = request_spkl.id 
                AND l.module_id = '$module_id' 
                AND l.approvalDate IS NOT NULL 
                AND l.ApprovalAction != '1'
                ORDER BY l.approvalDate DESC)";

            // Subquery: next approver name
            $subqueryNextApprover = "(SELECT TOP 1 e.FullName
                FROM tbl_approverListReq l
                JOIN tbl_approver a ON l.approver_id = a.id
                JOIN employee.tbl_employee e ON a.employee_id = e.id
                WHERE l.req_id = request_spkl.id 
                AND l.module_id = '$module_id' 
                AND l.ApprovalAction = '1'
                ORDER BY a.sequence ASC)";

            $data = $dataquery
                ->selectRaw("
                    request_spkl.*,
                    codes.code,
                    emp.FullName,
                    emp.SAPID,
                    designation.DesignationName,
                    CASE WHEN request_spkl.user_id = '$user_id' THEN 1 ELSE 0 END AS isMine,
                    $subqueryPending AS isPendingOnMe,
                    $subqueryLastApproval AS lastApprovalDate,
                    $subqueryNextApprover AS nextApproverName
                ")
                ->leftJoin('codes', 'request_spkl.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee as emp', 'request_spkl.employee_id', '=', 'emp.id')
                ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id')
                ->with(['user', 'approverlist', 'spkl_detail'])
                ->where(function ($query) use ($subqueryPending, $user_id) {
                    $query->whereRaw("$subqueryPending = 1")
                        ->orWhere(function ($query) use ($user_id) {
                            $query->where('request_spkl.user_id', '!=', $user_id)
                                ->whereIn('request_spkl.requestStatus', [1, 2, 3, 4]);
                        })
                        ->orWhere('request_spkl.user_id', $user_id);
                })
                ->orderByDesc('request_spkl.created_at')
                ->get();

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request, $id)
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

            if (!$employee || !isset($employee->companycode)) {
                throw new \Exception("Data employee atau kolom 'bu' tidak ditemukan.");
            }

            // Ambil DeptHead berdasarkan sys_id_depthead
            $deptHead = DB::table('employee.tbl_employee')
                ->where('sys_id', $employee->sys_id_depthead)
                ->select('id')
                ->first();

            if (!$deptHead) {
                throw new \Exception("Data DeptHead tidak ditemukan.");
            }

            $requestData['user_id'] = $user->id;
            $requestData['employee_id'] = $employee->id;
            $requestData['bu'] = $employee->companycode;
            $requestData['DeptHead'] = $deptHead->id;
            $requestData['tms'] = 0;
            $requestData['morethantwohours'] = 0;

            
            $newData = $this->model->create($requestData);
            DB::commit();
            
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

            $data = $this->model->select('request_spkl.*','codes.code')
            ->leftJoin('codes','request_spkl.code_id','codes.id')
            ->where('request_spkl.id',$id)
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

            // $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

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

    public function genPdfSpkl(Request $request, $id) 
    {
        $dataAppr = DB::table('spklApprover')->select('*')->where('id', $id)->get(); // Data approver
        // $requestspklDetail = DB::table('request_spkl_detail')->select('work_date', 'remarks', 'reason')->where('req_id', $id)->get();
        $dataDetail = DB::table('request_spkl_detail as rsd')
        ->select('rsd.*', 'dept.DepartmentName', 'emp.FullName', 'emp.SAPID', 'des.DesignationName')
        ->leftJoin('employee.tbl_employee as emp', 'rsd.employee_id', '=', 'emp.id')
        ->leftJoin('employee.tbl_department as dept', 'emp.department_id', '=', 'dept.id')
        ->leftJoin('employee.tbl_designation as des', 'emp.designation_id', '=', 'des.id')
        ->where('rsd.req_id', $id)
        ->get();

        $data = $this->model->select(
            'request_spkl.*',
            'codes.code',
            'users.fullname',
            'emp.SAPID',
            'designation.DesignationName',
            'sup.FullName as superior_name',
            'level.id as level',
            'alh_sub.fullname as submitter_name',
            'alh_sub.approvalDate as submit_date'
        )
        ->leftJoin('codes', 'request_spkl.code_id', '=', 'codes.id')
        ->leftJoin('users', 'request_spkl.user_id', '=', 'users.id')
        ->leftJoin('employee.tbl_employee as emp', 'request_spkl.employee_id', '=', 'emp.id')
        ->leftJoin('request_spkl_detail as rwd', 'request_spkl.id', '=', 'rwd.req_id')
        ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id')
        ->leftJoin('employee.tbl_location as loc', 'emp.location_id', '=', 'loc.id')
        ->leftJoin('employee.tbl_employee as sup', 'request_spkl.Superior', '=', 'sup.id')
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
        ) as alh_sub"), 'alh_sub.req_id', '=', 'request_spkl.id')
        ->where('request_spkl.id', $id)
        ->first();

        if (!$data || !$dataAppr) {
            return response()->json(["status" => "error", "message" => "Data or dataappr not found"]);
        }

        // dd($data, $dataAppr, $dataDetail);
    
        try {
            $excel = new COM("Excel.Application");
            $excel->Visible = false;

            $file = public_path("template/spkl/spkl.xlsx");

            if (!file_exists($file)) {
                throw new \Exception("File tidak ditemukan: " . $file);
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets(1);
            $Worksheet->Activate();

            $row = 15;
            $counter = 1;

            foreach ($dataDetail as $detail) {
                // Nomor urut di kolom C
                $Worksheet->Range("C{$row}")->Value = $counter;

                // Data lainnya
                $Worksheet->Range("E{$row}")->Value = (string) $detail->FullName;
                $Worksheet->Range("G{$row}")->Value = (string) $detail->SAPID;
                $Worksheet->Range("H{$row}")->Value = (string) $detail->DesignationName;

                $row++;
                $counter++;
            }

            // Isi Form Data
            $Worksheet->Range("G8")->Value = $data->bu;
            $Worksheet->Range("N9")->Value = $data->work_date;
            $picpath = public_path("assets/images/approved.png");

            if (file_exists($picpath)) {

                function addPictureRespectTemplate($Worksheet, $picPath, $labelCellAddress, $stampRow, $height, $excel, $nudgeX = 0, $nudgeY = 0) {
                    $labelRange = $Worksheet->Range($labelCellAddress);
                    $labelRow = $labelRange->Row;
                    $labelCol = $labelRange->Column;
                    $cell = $excel->Cells($labelRow, $labelCol);

                    $isMerged = false;
                    try { $isMerged = (bool)$cell->MergeCells; } catch (Exception $e) { $isMerged = false; }

                    if ($isMerged) {
                        $mergeArea = $cell->MergeArea;
                        $startCell = $mergeArea->Cells(1,1);
                        $lastCell = $mergeArea->Cells($mergeArea->Rows->Count, $mergeArea->Columns->Count);
                        $areaLeft = $startCell->Left;
                        $areaWidth = ($lastCell->Left + $lastCell->Width) - $areaLeft;
                    } else {
                        $areaLeft = $cell->Left;
                        $areaWidth = $cell->Width;
                    }

                    $stampCell = $excel->Cells($stampRow, $labelCol);
                    $areaTop = $stampCell->Top;
                    $areaHeight = $stampCell->Height;

                    $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                    $pic->LockAspectRatio = True;
                    $pic->Height = $height;

                    $tries = 0;
                    while ((empty($pic->Width) || empty($pic->Height)) && $tries < 30) {
                        usleep(10000);
                        $tries++;
                    }

                    $targetLeft = $areaLeft + (($areaWidth - $pic->Width) / 2) + $nudgeX;
                    $targetTop  = $areaTop  + (($areaHeight - $pic->Height) / 2) + $nudgeY;

                    $pic->Left = $targetLeft;
                    $pic->Top  = $targetTop;

                    try { $pic->Placement = 2; } catch (Exception $e) {}

                    return ['Left'=>$pic->Left,'Top'=>$pic->Top,'W'=>$pic->Width,'H'=>$pic->Height];
                }

                foreach ($dataAppr as $appr) {
                    if ($appr->approvalAction == 3) {
                        if ($appr->sequence == 2) {
                            $Worksheet->Range("D35")->Value = $appr->apprname;
                            $Worksheet->Range("D36")->Value = $appr->approvalDate;
                            addPictureRespectTemplate($Worksheet, $picpath, "D35", 32, 36, $excel, 0, 0);
                        }
                        if ($appr->sequence == 3) {
                            $Worksheet->Range("G35")->Value = $appr->apprname;
                            $Worksheet->Range("G36")->Value = $appr->approvalDate;
                            addPictureRespectTemplate($Worksheet, $picpath, "G35", 32, 36, $excel, 0, 0);
                        }
                        if ($appr->sequence == 4) {
                            $Worksheet->Range("J35")->Value = $appr->apprname;
                            $Worksheet->Range("J36")->Value = $appr->approvalDate;
                            addPictureRespectTemplate($Worksheet, $picpath, "J35", 32, 36, $excel, 0, 0);
                        }
                        if ($appr->sequence == 5) {
                            $Worksheet->Range("N35")->Value = $appr->apprname;
                            $Worksheet->Range("N36")->Value = $appr->approvalDate;
                            addPictureRespectTemplate($Worksheet, $picpath, "N35", 32, 36, $excel, 0, 0);
                        }
                    }
                }
            }

            // Ekspor ke PDF
            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/spkl/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/spkl/pdf/' . $fileName;
            DB::table('request_wphc')
            ->where('id', $id) 
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);

			return $pathfilename;
        } catch (\Exception $e) {
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-spkl', $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Error di " . $e->getFile() . " baris " . $e->getLine() . ": " . $e->getMessage()
            ]);
        }
    }
}