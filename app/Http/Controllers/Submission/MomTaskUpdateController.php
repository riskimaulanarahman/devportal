<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionMail;

use App\Models\Module;
use App\Models\Submission\MomTaskUpdate;
use App\Models\Submission\MomTaskBound;
use App\Models\Submission\MomTask;
use App\Models\Stackholders;
use App\Models\Code;
use App\Models\User;
use DB;
use PDF;

class MomTaskUpdateController extends Controller
{

    private $model;
    public $modulename;
    public $taskbound;

    public function __construct()
    {
        $this->model = new MomTaskUpdate();
        $this->modulename = 'Mom';
        $this->module = new Module();
        $this->taskbound = new MomTaskBound();
    }

    public function index()
    {
        try {

            $data = $this->model->all();

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {

            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $requestData['updated_by'] = $this->getEmployeeID()->id;
            $requestData['date'] = date('Y-m-d');

            $getTaskBound = $this->taskbound->where('task_id',$request->task_id)
            ->where('employee_id',$this->getEmployeeID()->id)
            ->get();

            if(count($getTaskBound) < 1) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            DB::beginTransaction();
            try {

                $store = $this->model->create($requestData);
                // Setelah create data, dapatkan ID dari $store
                $storeId = $store->id;

                // Update kolom status pada Model MomTask where task_id = $request->task_id
                $momTask = MomTask::where('id', $request->task_id)
                ->where('status', 'Open')
                ->first();

                if ($momTask) {
                    $momTask->status = 'Progress'; // Update status sesuai kebutuhan Anda
                    $momTask->save();
                }

                // START NORIFICATION
                $this->generateNotificationMessage($request->task_id, $mode = 'Add', $this->getAuth(), null);
                // END NORIFICATION

                DB::commit();

                return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function getList($id,$modulename)
    {
        try {
            $module = $this->module->select('id','module')->where('module',$modulename)->first();
            if($module) {
                $data = $this->model->where('task_id',$id)
                ->orderBy('updated_at','desc')
                ->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            } else {
                return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->model->findOrFail($id);
            
            $requestData = $request->all();
            $requestData['updated_by'] = $this->getEmployeeID()->id;
            $requestData['date'] = date('Y-m-d');

            $getTaskBound = $this->taskbound->where('task_id',$data->task_id)
            ->where('employee_id',$this->getEmployeeID()->id)
            ->get();

            if(count($getTaskBound) < 1) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            $data->update($requestData);

             // START NORIFICATION
             $this->generateNotificationMessage($data->task_id, $mode = 'Update', $this->getAuth(), null);
             // END NORIFICATION

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);

            $getTaskBound = $this->taskbound->where('task_id',$data->task_id)
            ->where('employee_id',$this->getEmployeeID()->id)
            ->get();

            if(count($getTaskBound) < 1) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function taskactions(Request $request, $id, $modulename) 
    {

            $data = MomTask::findOrFail($id);
            $requestData = $request->all();
            if($request->status == 'Completed') {
                $requestData['completion_date'] = date('Y-m-d');
                $requestData['status'] = 'Done';
                $approvalAction = 3; // approved/completed
            } else if ($request->status == 'Reworked'){
                $requestData['status'] = 'Progress';
                $approvalAction = 2; // reworked
            }

        DB::beginTransaction();
        try {

            $data->update($requestData);

            $getCategoryReqID = DB::table('tbl_category')->where('id',$data->category_id)->first();

            //start save history perubahan
            $fields = [
                'status' => $request->status.' | Task Description : '.$data->description.' | Remarks : '.$request->remarks,
            ];
            
            foreach ($fields as $key => $value) {
                if ($value) {
                    $this->approverAction($this->modulename, $getCategoryReqID->req_id, $key, $approvalAction, $value, null);
                }
            }
            //end save history perubahan

            // START NORIFICATION
            $this->generateNotificationMessage($id, $mode = $request->status, $this->getAuth(), $request->remarks);
            // END NORIFICATION

            DB::commit();
            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function generateNotificationMessage($id, $mode, $userupdate, $remarks) {
        $getMomID = DB::table('request_momTask')->select('tbl_category.req_id','tbl_category.category','request_momTask.id','request_momTask.description')
                        ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
                        ->where('request_momTask.id',$id)
                        ->first();
        $getSubmissionData = DB::table('request_mom')->where('id', $getMomID->req_id)->first();
        $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator

        if($mode == "Completed") {
            $messages = "This post has new activities <b>Completed</b> on task <b>" .$getMomID->description. "</b> in the <b>" .$getMomID->category. "</b> category. <br> <b>Remarks</b> : ".$remarks;
        } else if($mode == "Reworked") {
            $messages = "This post has new activities <b>Reworked</b> on task <b>" .$getMomID->description. "</b> in the <b>" .$getMomID->category. "</b> category. <br> <b>Remarks</b> : ".$remarks;
        } else {
            $messages = "This post has a new activity from <b>" .$userupdate->fullname. "</b> on task <b>" .$getMomID->description. "</b> in the <b>" .$getMomID->category. "</b> category.";
        }

        $mailData = [
            "all" => 1,
            "action_id" => 0,
            "submission" => $getSubmissionData,
            "email" => $getCreator->email, // kirim kepada creator
            "fullname" => $getCreator->fullname,
            "message" => $messages,
            "remarks" => $mode,
            "highlightedTaskId" => $getMomID->id // Menambahkan ID task yang dihighlight
        ];
        if($getSubmissionData->requestStatus == 3) {
            Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
        }
    }

    public function reminderNotificationMessage($mode) {
        if($mode == 'reminder') {
            
            $reminders = [];
            $getReminderMom = DB::table('reminderMomDeadline')->get();
            foreach($getReminderMom as $r) {
                $reminders[] = $r->mom_id;
            }
            $uniqueReminders = array_unique($reminders);

            foreach($uniqueReminders as $g) {

                $getSubmissionData = DB::table('request_mom')->where('id', $g)->first();

                $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator

                $mailData = [
                    "all" => 1,
                    "action_id" => 0,
                    "submission" => $getSubmissionData,
                    "email" => $getCreator->email, // kirim kepada creator
                    "fullname" => $getCreator->fullname,
                    "message" => $this->mailMessage()['deadlineTaskReminder'],
                    "remarks" => null,
                    "highlightedTaskId" => null
                ];

                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
            }
        }


    }

    public function genPdfMom($id) {

        $getSubmissionData = DB::table('request_mom')->where('id', $id)->first();
        $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator
        $code = Code::findOrFail($getSubmissionData->code_id);
        $code = $code->code;
        $final = 1;

        $mailData = [
            "all" => 1,
            "pdf" => 1,
            "action_id" => 0,
            "submission" => $getSubmissionData,
            // "email" => $getCreator->email,
            "fullname" => $getCreator->fullname,
            "message" => $this->mailMessage()['momTaskSummary'],
            "remarks" => null,
        ];
        // dd($mailData);
        $assignment = Stackholders::leftJoin('employee.tbl_employee','tbl_stackholders.employee_id','=','employee.tbl_employee.id')
                        ->leftJoin('users','employee.tbl_employee.LoginName','=','users.username')
                        ->select('tbl_stackholders.*','users.email','employee.tbl_employee.FullName')
                        ->where('req_id',$id)
                        ->where('module_id',$this->getModuleId($this->modulename))
                        ->get();

        $latestUpdates = DB::table('request_momTaskUpdate')
        ->select('request_momTaskUpdate.*')
        ->whereIn('request_momTaskUpdate.id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('request_momTaskUpdate')
                ->groupBy('request_momTaskUpdate.task_id');
        });

        $detailmomtask = DB::table('tbl_category')
        ->select('tbl_category.category',
        'momTaskDetail.description',
        'momTaskDetail.section',
        'momTaskDetail.status',
        'momTaskDetail.deadline_date',
        'momTaskDetail.agings',
        'momTaskDetail.time_categorys',
        'request_momTaskBound.content',
        'ea.FullName',
        'latest_updates.description as UpdateDescription',
        'latest_updates.date as UpdateDate',
        'eb.FullName as UpdateName'
        )
        ->where('tbl_category.req_id',$id)
        ->leftJoin('momTaskDetail','tbl_category.id','momTaskDetail.category_id')
        ->leftJoin('request_momTaskBound','momTaskDetail.id','request_momTaskBound.task_id')
        ->leftJoinSub($latestUpdates, 'latest_updates', function($join) {
            $join->on('momTaskDetail.id', '=', 'latest_updates.task_id');
        })
        ->leftJoin('employee.tbl_employee as ea','request_momTaskBound.employee_id','ea.id')
        ->leftJoin('employee.tbl_employee as eb','latest_updates.updated_by','eb.id')
        ->get();

        // Load view dan passing data
        $pdf = PDF::loadView('emails.momrequestmail', compact(['detailmomtask','mailData','code','final','assignment']));
        $pdf->set_paper('letter', 'landscape');

        return $pdf->stream('document.pdf');
    }
}
