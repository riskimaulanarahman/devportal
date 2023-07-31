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
    // private $model;
    // public $modulename;
    public $module;
    // public $sendmail;

    public function __construct()
    {
        // $this->model = new Project();
        // $this->modulename = 'Project';
        $this->module = new Module();
        // $this->sendmail = new SubmissionMail();
    }

    public function submit(Request $request, $id, $modulename)
    {

            $locModel = "App\Models\Submission\\".$modulename;
            $model = new $locModel;
            $columns = $model->getFillableColumns();
            $tableName = $model->getTableName();

        try {

            $getSubmissionData = DB::table($tableName)->where('id', $id)->first();

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

            $attachement = DB::table('tbl_attachment')
                            ->where('req_id',$id)
                            ->where('module_id',$this->getModuleId($modulename))
                            ->get();
            
            if (count($nullColumns) > 0) {
                $nullColumnsStr = implode(', ', $nullColumns);
                return response()->json(["status" => "error", "message" => "Error: Column $nullColumnsStr is required"]);
            }

            if (count($attachement) < 1) {
                return response()->json(["status" => "error", "message" => "Error: Supporting document not found. Please attach it."]);
            }

            $final = 0;
            $mailData = [];

            $approverlist = ApproverListReq::where('req_id',$id)
                ->when($request->action == 'submission', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*')
                            ->where('module_id',$this->getModuleId($modulename));
                })
                ->when($request->action == 'approval', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*','tbl_approver.isFinal')
                                 ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                                 ->where('module_id',$this->getModuleId($modulename))
                                 ->where('tbl_approver.user_id', $this->getAuth()->id);
                })
                ->with('approvaluser')
                ->get();

            if ($request->requestStatus == 0) {
                $statusappr = 0;
                $requeststatus = $request->requestStatus;
                $this->approverAction($modulename, $id, 'Cancelled', 5 , null);
            } else if ($request->requestStatus == 1) {
                if($request->action == 'submission') {
                    $statusappr = 1;
                    $requeststatus = $request->requestStatus;
                    $this->approverAction($modulename, $id, 'Submitted', 1, null);
                    
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
                               $statusappr = 3;
                               $requeststatus = 1;
                            } else if($data->isFinal == 1) {
                                $final = 1;
                                $statusappr = 3;
                                $requeststatus = 3;
                            }
                        }
                    }
                    $this->approverAction($modulename, $id, 'Approver', $request->approvalAction, $request->remarks);
                }
            }

            foreach($approverlist as $appr) {
                $appr->approvalAction = $statusappr;
                $appr->update();
            }

            DB::table($tableName)
                ->where('id', $id)
                ->update([
                    "requestStatus" => $requeststatus
                ]);

            foreach($approverlist as $getappr) {
                $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator
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
                        "id" => 1,
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
                        "id" => 2,
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
                            "id" => 30,
                            "action_id" => 0,
                            "submission" => $getSubmissionData,
                            "email" => $getCreator->email, // kirim kepada creator
                            "fullname" => $getCreator->fullname,
                            "message" => $this->mailMessage()['approved'],
                        ];
                        break;
                    } else {
                        $getNextApprover = ApproverListReq::leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                            ->where('tbl_approverListReq.req_id',$getappr->req_id)
                            ->where('tbl_approverListReq.approvalAction',1)
                            ->orderBy('tbl_approver.sequence','asc')
                            ->first(); // get next approver
                        $getUser = User::findOrFail($getNextApprover->user_id); 
                        $mailData = [
                            "id" => 31,
                            "action_id" => 1,
                            "submission" => $getSubmissionData,
                            "email" => $getUser->email, // kirim kepada approver
                            "fullname" => $getUser->fullname,
                            "creator" => $getCreator->fullname,
                            "message" => $this->mailMessage()['waitingapproval'],
                        ];
                        break;

                    }

                }
                if($request->approvalAction == 4 && $getappr->approvalAction == 4) { // rejected pengajuan
                    $mailData = [
                        "id" => 4,
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
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData));
            }
 
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);
            
        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);

        }
    }

   
}
