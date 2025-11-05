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
use Carbon\Carbon;

use App\Mail\SubmissionMail;

class SpklTimesheetController extends Controller
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
                // ->where('request_spkl.requestStatus', 3)
                ->where('request_spkl.tms', 34)
                ->where(function ($query) use ($subqueryPending, $user_id) {
                    $query->whereRaw("$subqueryPending = 1")
                        ->orWhere('request_spkl.user_id', $user_id)
                        ->orWhere('request_spkl.user_id', '!=', $user_id);
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

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->getAuth();
            $requestData = $request->all();

            $user_id = $this->getAuth()->id;
            $requestData['user_id'] = $user_id;

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

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'ActualStartWork' => 'nullable|date',
    //             'ActualEndWork' => 'nullable|date',
    //             'ActualNormalHours' => 'nullable|numeric',
    //             'ActualTotalHours' => 'nullable|numeric',
    //             'ActualOvertimeHours' => 'nullable|numeric',
    //         ]);

    //         $this->addOneDayToDate($validated);

    //         $data = $this->model->findOrFail($id);
    //         $totalHours = 0;
    //         if (!empty($validated['ActualStartWork']) && !empty($validated['ActualEndWork'])) {
    //             try {
    //                 $start = Carbon::parse($validated['ActualStartWork']);
    //                 $end = Carbon::parse($validated['ActualEndWork']);

    //                 if ($end->greaterThan($start)) {
    //                     $totalHours = floor($end->floatDiffInRealHours($start)); // tanpa koma
    //                 }
    //             } catch (\Exception $e) {
    //                 $totalHours = 0;
    //             }
    //         } elseif (isset($validated['ActualTotalHours'])) {
    //             $totalHours = intval($validated['ActualTotalHours']);
    //         }

    //         $validated['ActualTotalHours'] = $totalHours;
    //         if (!isset($validated['ActualNormalHours']) || $validated['ActualNormalHours'] === null) {
    //             $dateSource = $validated['ActualStartWork'] ?? $validated['ActualEndWork'] ?? null;

    //             if ($dateSource) {
    //                 try {
    //                     $day = Carbon::parse($dateSource)->dayOfWeek; // 0 = Minggu, 8 = Weekday, ..., 4 = Sabtu

    //                     switch ($day) {
    //                         case Carbon::SUNDAY:
    //                             $validated['ActualNormalHours'] = 0;
    //                             break;
    //                         case Carbon::SATURDAY:
    //                             $validated['ActualNormalHours'] = 4;
    //                             break;
    //                         default:
    //                             $validated['ActualNormalHours'] = 8;
    //                             break;
    //                     }
    //                 } catch (\Exception $e) {
    //                     $validated['ActualNormalHours'] = 8;
    //                 }
    //             } else {
    //                 $validated['ActualNormalHours'] = 8; 
    //             }
    //         }

    //         $normal = is_numeric($validated['ActualNormalHours']) ? floatval($validated['ActualNormalHours']) : 0;
    //         $validated['ActualOvertimeHours'] = max(0, $totalHours - $normal);
    //         if ($request->DeptHead) {
    //             $this->createApprDeptHead($request->DeptHead, $this->modulename, $id);
    //         }
    //         $data->update($validated);
    //         if (isset($request->ticketStatus) && $data->requestStatus == 3) {
    //             $getSubmissionData = $data;
    //             $mailData = [
    //                 "id" => 30,
    //                 "action_id" => 5,
    //                 "submission" => $getSubmissionData,
    //                 "email" => $this->getUserByid($getSubmissionData->user_id)->email,
    //                 "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
    //                 "message" => $this->mailMessage()['newActivity'],
    //                 "remarks" => $request->ticketStatus
    //             ];
    //             Mail::to($mailData['email'])->send(new SubmissionMail($mailData, $this->modulename, 1));
    //         }

    //         return response()->json([
    //             'status' => "success",
    //             'message' => $this->getMessage()['update']
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $ve) {
    //         return response()->json(["status" => "error", "message" => $ve->errors()], 422);
    //     } catch (\Exception $e) {
    //         return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
    //     }
    // }

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

    public function genPdfeSpkls(Request $request, $id) 
    {
        $dataAppr = DB::table('spklApprover')->select('*')->where('id', $id)->get(); // Data approver
        // $requestspklDetail = DB::table('request_spkl_detail')->select('work_date', 'remarks', 'reason')->where('req_id', $id)->get();
        $dataDetail = DB::table('request_spkl_detail')->where('req_id', $id)->get();


        $data = $this->model->select(
            'request_spkl.*',
            'codes.code',
            'users.fullname',
            'emp.SAPID',
            // 'emp.loginName',
            // 'emp.FullName',
            'designation.DesignationName',
            // 'rwd.work_date',
            // 'rwd.remarks',
            // 'rwd.reason',
            // 'loc.Location',
            // 'usr.email',
            'sup.FullName as superior_name',
            // 'sup_usr.email as superior_email',
            // 'emp_usr.email as employee_email',
            // 'emp_usr.fullname as employee_name',
            'level.id as level',
            'alh_sub.fullname as submitter_name',
            'alh_sub.approvalDate as submit_date'
        )
        ->leftJoin('codes', 'request_spkl.code_id', '=', 'codes.id')
        ->leftJoin('users', 'request_spkl.user_id', '=', 'users.id')
        ->leftJoin('employee.tbl_employee as emp', 'request_spkl.employee_id', '=', 'emp.id')
        // ->leftJoin('users as usr', 'emp.LoginName', '=', 'usr.username')
        ->leftJoin('request_spkl_detail as rwd', 'request_spkl.id', '=', 'rwd.req_id')
        ->leftJoin('employee.tbl_designation as designation', 'emp.designation_id', '=', 'designation.id')
        ->leftJoin('employee.tbl_location as loc', 'emp.location_id', '=', 'loc.id')
        ->leftJoin('employee.tbl_employee as sup', 'request_spkl.Superior', '=', 'sup.id')
        // ->leftJoin('users as emp_usr', 'emp.LoginName', '=', 'emp_usr.username')
        // ->leftJoin('users as sup_usr', 'sup.LoginName', '=', 'sup_usr.username')
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

        dd($data, $dataAppr, $dataDetail);
    

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

            $row = 37;
            foreach ($dataDetail as $detail) {
                $Worksheet->Range("B{$row}")->Value = (string) $detail->work_date;
                $Worksheet->Range("D{$row}")->Value = (string) $detail->remarks;
                $Worksheet->Range("K{$row}")->Value = (string) $detail->reason;
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
                    $Worksheet->Range("K{$row}")->Value = (string) $detail->reason;

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
            DB::table('request_spkl')
            ->where('id', $id) // Sesuaikan dengan primary key di tabel
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);

			return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-spkl', $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Error di " . $e->getFile() . " baris " . $e->getLine() . ": " . $e->getMessage()
            ]);
        }
    }
}
