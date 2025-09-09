<?php

namespace App\Http\Controllers\Submission;
	
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Models\Submission\Project;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\User;
use App\Mail\SubmissionMail;
use DB;

class SubmissionController extends Controller
{
    public $module;

    public function __construct()
    {
        $this->module = new Module();
    }

    public function checkFields(Request $request, $reqid, $modulename)
    {
        $namespaceMap = [
            'ActiveDirectory' => "App\Models\Submission\IT",
            'Mmf28' => "App\Models\Submission\MMF",
            'Mmf30' => "App\Models\Submission\MMF",
            'MaterialReq' => "App\Models\Submission\Ecatalog",
            'Advance' => "App\Models\Submission\Financial\Advance",
            'Hcrf' => "App\Models\Submission\HRIS\Hcrf",
            'Memorandum' => "App\Models\Submission\Memorandum",
            // 'Legal' => "App\Models\Submission\Legal",
        ];

        $modulesUsingId = ['JDI'];
        
        $baseNamespace = "App\Models\Submission";
        $locModel = $baseNamespace . "\\" . $modulename;

        if (!class_exists($locModel)) {
            if (array_key_exists($modulename, $namespaceMap)) {
                $locModel = $namespaceMap[$modulename] . "\\" . $modulename;
            }
        }
        $model = new $locModel;
        $columns = $model->getFillableColumns();
        $tableName = $model->getTableName();
        // dd($tableName);
        try {
            // Extract the fields to check from the request payload
            $fieldsToCheck = $request->input('fieldsToCheckGrid');

            if(!$fieldsToCheck) {
                return response()->json(['status' => 'show']);
            }

            // Determine the column to use based on the module name
            $columnToCheck = in_array($modulename, $modulesUsingId) ? 'id' : 'req_id';

            // Fetch the data for the given reqid
            $record =DB::table($tableName)->where($columnToCheck, $reqid)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Check if specified fields are null or empty
            $missingFields = [];
            foreach ($fieldsToCheck as $fieldInfo) {
                // dd($fieldInfo);
                $fieldValue = $fieldInfo['field'];
                $fieldName = $fieldInfo['name'];
                if (is_null($record->$fieldValue) || $record->$fieldValue === '') {
                    $missingFields[] = $fieldName;
                }
            }

            $textMissing = "Please fill all required fields : ".implode(",",$missingFields);

            if (!empty($missingFields)) {
                return response()->json(['status' => 'error', 'message' => $textMissing]);
            }

            return response()->json(['status' => 'show']);
            
        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function submit(Request $request, $id, $modulename)
    {
        DB::beginTransaction();

            // $locModel = "App\Models\Submission\\".$modulename;

            $namespaceMap = [
                'ActiveDirectory' => "App\Models\Submission\IT",
                'Mmf' => "App\Models\Submission\MMF",
                'MaterialReq' => "App\Models\Submission\Ecatalog",
                'Hris' => "App\Models\Submission\HRIS",
                'Ccm' => "App\Models\Submission\Financial",
                'Capex' => "App\Models\Submission\Financial",
                'Memorandum' => "App\Models\Submission\Memorandum",
                'Legal' => "App\Models\Submission\Legal",
            ];

            $baseNamespace = "App\Models\Submission";
            $locModel = $baseNamespace . "\\" . $modulename;

            if (!class_exists($locModel)) {
                if (array_key_exists($modulename, $namespaceMap)) {
                    $locModel = $namespaceMap[$modulename] . "\\" . $modulename;
                }
            }
            $model = new $locModel;
            $columns = $model->getFillableColumns();
            $tableName = $model->getTableName();

        try {

            $getSubmissionData = DB::table($tableName)->where('id', $id)->first();
            $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator  
            // dd($getCreator);          
            // dd($tableName, $id, $getSubmissionData);
            
            $nullColumns = [];

            foreach ($columns as $column) {
                $data = DB::table($tableName)
                            ->where('id', $id)
                            ->whereNull($column)
                            ->first();

                if ($data) {
                    $nullColumns[] = $column;
                }
            }

            $module_id = $this->getModuleId($modulename);

            // attachment
            $queryAttachement = DB::table('tbl_attachment')
                                ->where('req_id',$id)
                                ->where('module_id',$module_id);

            if ($modulename == 'Jdi') {
                $queryAttachement->where(function($query) {
                    $query->where('remarks', 'like', 'before')
                          ->orWhere('remarks', 'like', 'after');
                });
            }
            if ($modulename == 'Legal') {
                $queryAttachement->where(function($query) {
                    $query->where('remarks', 'like', 'RFC')
                          ->orWhere('remarks', 'like', 'Surat Perjanjian');
                });
            }

            $attachement = $queryAttachement->get();
            // end attachment

            $checkAppr = DB::table('tbl_approverListReq')
                ->where('req_id',$id)
                ->where('module_id',$module_id)
                ->get();

            if($modulename == 'Ticket' || $modulename == 'UavMission' || $modulename == 'Hrsc') {
                $assignment = DB::table('tbl_assignment')
                ->where('req_id',$id)
                ->where('module_id',$module_id)
                ->get();
            }

            if (count($nullColumns) > 0) {
                $nullColumnsStr = implode(', ', $nullColumns);
                return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Column $nullColumnsStr is required"]);
            }

            if($modulename == 'UavMission') {
                $uavmissiondetail = DB::table('request_uavmissiondetail')
                ->where('req_id',$id)
                ->where('module_id',$module_id)
                ->get();

                if (count($uavmissiondetail) < 1) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Detail not found. Please input the correct information."]);
                }
            }
            if ($modulename == 'Jdi') {
                $hasBefore = false;
                $hasAfter = false;

                foreach ($attachement as $attc) {
                    if ($attc->remarks === 'Before') {
                        $hasBefore = true;
                    }
                    if ($attc->remarks === 'After') {
                        $hasAfter = true;
                    }
                }

                if (!$hasBefore) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Supporting document 'Before' is required. Please attach it."]);
                }

                if (!$hasAfter) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Supporting document 'After' is required. Please attach it."]);
                }
            } 
            if ($modulename == 'Legal') {
                $hasRfc = false;
                $hasSp = false;

                foreach ($attachement as $attc) {
                    if ($attc->remarks === 'RFC') {
                        $hasRfc = true;
                    }
                    if ($attc->remarks === 'Surat Perjanjian') {
                        $hasSp = true;
                    }
                }

                if (!$hasRfc) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Supporting document 'RFC' is required. Please attach it."]);
                }

                if (!$hasSp) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Supporting document 'Surat Perjanjian' is required. Please attach it."]);
                }
            } else {
                // submission yang tidak perlu menambahkan supporting document
                $except = [
                    'ActiveDirectory',
                    'Mmf',
                    'MaterialReq',
                    'Hris',
                    'Ghm',
                    'Memorandum'
                ];
                if (!in_array($modulename, $except)) {
                    if (count($attachement) < 1) {
                        return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Supporting document not found. Please attach it."]);
                    }
                }
            }

            if($modulename == 'Mom') {
                $checkTaskMom = DB::table('request_momTask')
                ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
                ->where('tbl_category.req_id',$id)
                ->where('tbl_category.module_id',$module_id)
                ->get();

                if (count($checkTaskMom) < 1) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Task Detail not found. Please input the correct information."]);
                }

                if (count($checkAppr) < 1) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: ApproverList not found. Please ". ($modulename == 'Mom') ? "Select Chairman From Participant" : "add approver."]);
                }
            }
            if($modulename == 'Memorandum') {
                
            }

            $final = 0;
            $mailData = [];

            // get and update approver list
            // dd($getSubmissionData);
            if($getSubmissionData->requestStatus == 0 || $getSubmissionData->requestStatus == 2) {
                $company = null;
                $category = null;

                if (in_array("category_id", $columns)) {
                    $category = $getSubmissionData->category_id;
                }

                if (in_array("bu", $columns) && in_array("sector", $columns)) {
                    $bu = $getSubmissionData->bu;
                    $sector = $getSubmissionData->sector;

                    if($sector !== null) {
                        if($sector == 'HO') {
                            $company = $bu.'-'.$sector;
                        } else {
                            $company = $sector;
                        }
                    } else {
                        $company = $bu;
                    }
                } else if(in_array("bu", $columns)) {
                    $bu = $getSubmissionData->bu;
                    $company = $bu;
                }
                    $this->createApprover($modulename, $id, $company, $category);

            }

            $approverlist = ApproverListReq::where('req_id',$id)
                ->when($request->action == 'submission', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*')
                            ->where('module_id',$this->getModuleId($modulename))
                            ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                            ->orderBy('tbl_approver.sequence','asc');
                })
                ->when($request->action == 'approval', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*','tbl_approver.isFinal')
                                 ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                                 ->where('module_id',$this->getModuleId($modulename))
                                 ->where('tbl_approver.user_id', $this->getAuth()->id)
                                 ->orderBy('tbl_approver.sequence','asc');
                })
                ->with('approvaluser')
                ->get();

            $rawgetapproverlist = ApproverListReq::where('req_id',$id)
                ->where('module_id',$module_id)
                ->where('approvalAction',1);

            $getapproverlist = $rawgetapproverlist->count();

            $dataapproversamecount = 0;
            $dataapproverlist = $rawgetapproverlist->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')->get();
            if ($dataapproverlist->count() == 2) {
                
                if ($dataapproverlist[0]->approvaluser->user_id == $dataapproverlist[1]->approvaluser->user_id) {
                    $dataapproversamecount = 1;
                }
            }
            
            if ($request->requestStatus == 0) {
                $statusappr = 0;
                $requeststatus = $request->requestStatus;
                $this->approverAction($modulename, $id, 'Cancelled', 5 , null, null); // $moduleName, $req_id, $type, $appraction, $remarks, appruser
            } else if ($request->requestStatus == 1) {
                if($request->action == 'submission') {
                    
                    $statusappr = 1;
                    $requeststatus = $request->requestStatus;
                    $this->approverAction($modulename, $id, 'Submitted', 1, null, null); // $moduleName, $req_id, $type, $appraction, $remarks, appruser

                    foreach($approverlist as $getappr) {
                        $getUser = User::findOrFail($getappr->approvaluser->user_id); // get approver
                        $mailData = [
                            "id" => 1,
                            "action_id" => 1,
                            "submission" => $getSubmissionData,
                            "email" => $getUser->email, // kirim kepada approver
                            "fullname" => $getUser->fullname,
                            "creator" => $getCreator->fullname,
                            "message" => $this->mailMessage()['waitingapproval'],
                        ];
                    }

                } else if($request->action == 'approval') {
                    if($request->approvalAction == 4) {
                        $statusappr = 4;
                        $requeststatus = 4;
                    } else if($request->approvalAction == 2) {
                        $statusappr = 2;
                        $requeststatus = 2;
                    } else if($request->approvalAction == 3) {

                        foreach($approverlist as $data) {
                            
                            if($data->isFinal == 0) {
                                if ($getapproverlist == 1 || $dataapproversamecount == 1) {
                                    $final = 1;
                                    $statusappr = 3;
                                    $requeststatus = 3;
                                } else {
                                    $statusappr = 3;
                                    $requeststatus = 1;
                                }
                            } else if($data->isFinal == 1) {
                                $final = 1;
                                $statusappr = 3;
                                $requeststatus = 3;
                            }
                            
                        }

                    }
                    $getcurrentapprUser = $approverlist[0]->approver_id;
                    $this->approverAction($modulename, $id, 'Approver', $request->approvalAction, $request->remarks, $getcurrentapprUser); // $moduleName, $req_id, $type, $appraction, $remarks, appuser
                }
            }


            if($final == 1) {
                if($modulename == 'Ticket' || $modulename == 'Hrsc') {
                    if (count($assignment) < 1) {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['assignmentnotfound']]);
                    }
                }
            }

            foreach($approverlist as $appr) {
                $appr->approvalAction = $statusappr;
                if($statusappr !== 3) {
                    $appr->approvalDate = null;
                }
                // $appr->approvalDate = null;
                $appr->update();
            }

            // Data untuk update
            $dataToUpdate = [
                "requestStatus" => $requeststatus
            ];

            // Cek jika modulename adalah 'Jdi' dan tambahkan submitDate
            if ($modulename == 'Jdi' && $request->action == 'submission') {
                $dataToUpdate["submitDate"] = Carbon::now(); // Menggunakan Carbon untuk mendapatkan tanggal dan waktu saat ini
            }
            // Cek jika modulename adalah 'Legal' dan tambahkan submitDate
            if ($modulename == 'Legal' && $request->action == 'submission') {
                $dataToUpdate["submitDate"] = Carbon::now(); // Menggunakan Carbon untuk mendapatkan tanggal dan waktu saat ini
                // $dataToUpdate["submissionDate"] = Carbon::now(); // Menggunakan Carbon untuk mendapatkan tanggal dan waktu saat ini
            }

            DB::table($tableName)
                ->where('id', $id)
                ->update($dataToUpdate);

            foreach($approverlist as $getappr) {
                if($request->approvalAction == 0 && $getappr->approvalAction == 0) { // cancel pengajuan
                    $mailData = [
                        "id" => 0,
                        "action_id" => 0, // action cancel
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['cancelled'],
                    ];
                    break;
                }
                if($request->approvalAction == 1 && $getappr->approvalAction == 1) { // submit pengajuan
                    $getUser = User::findOrFail($getappr->approvaluser->user_id); // get approver
                    $mailData = [
                        "id" => 1, // first approval
                        "action_id" => 1,
                        "submission" => $getSubmissionData,
                        "email" => $getUser->email, // kirim kepada approver
                        "fullname" => $getUser->fullname,
                        "creator" => $getCreator->fullname,
                        "message" => $this->mailMessage()['waitingapproval'],
                    ];
                    break;
                }
                if($request->approvalAction == 2 && $getappr->approvalAction == 2) { // rework pengajuan
                    $mailData = [
                        "id" => 2, // reworked approval
                        "action_id" => 0,
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['reworked'],
                        "remarks" => $request->remarks
                    ];
                    break;
                }
                if($request->approvalAction == 3 && $getappr->approvalAction == 3) { // approved pengajuan
                    
                    if($final == 1) {
                        $mailData = [
                            "id" => 30, // final approved
                            "action_id" => 0,
                            "submission" => $getSubmissionData,
                            "email" => $getCreator->email, // kirim kepada creator
                            "fullname" => $getCreator->fullname,
                            "message" => $this->mailMessage()['approved'],
                            "remarks" => $request->remarks
                        ];
                        break;
                    } else {
                        $getNextApprover = ApproverListReq::leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                            ->where('tbl_approverListReq.req_id',$getappr->req_id)
                            ->where('tbl_approverListReq.approvalAction',1)
                            ->where('tbl_approverListReq.module_id', $module_id)
                            ->orderBy('tbl_approver.sequence','asc')
                            ->first(); // get next approver
                        $getUser = User::findOrFail($getNextApprover->user_id); 
                        $mailData = [
                            "id" => 31, // next approved
                            "action_id" => 1,
                            "submission" => $getSubmissionData,
                            "email" => $getUser->email, // kirim kepada approver
                            "fullname" => $getUser->fullname,
                            "creator" => $getCreator->fullname,
                            "message" => $this->mailMessage()['waitingapproval'],
                        ];
                        // dd($mailData);
                        break;
                    }

                }
                if($request->approvalAction == 4 && $getappr->approvalAction == 4) { // rejected pengajuan
                    $mailData = [
                        "id" => 4, // reject
                        "action_id" => 0,
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['rejected'],
                        "remarks" => $request->remarks
                    ];
                    break;
                }
            }
            
            if(count($mailData) > 0) {
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,$final));
            }

            DB::commit();
 
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);
            
        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);

        }
    }   
}
