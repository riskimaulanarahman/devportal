<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionMail;

use App\Models\Module;
use App\Models\Submission\MomTaskUpdate;
use App\Models\Submission\MomTaskBound;
use App\Models\User;
use DB;

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
        try {

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

            // START NORIFICATION
            $this->generateNotificationMessage($request->task_id, $mode = 'Add');
            // END NORIFICATION

            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

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
            
            // START NORIFICATION
                $this->generateNotificationMessage($data->task_id, $mode = 'Update');
            // END NORIFICATION

            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function generateNotificationMessage($id, $mode) {
        $getMomID = DB::table('request_momTask')->select('tbl_category.req_id')
                        ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
                        ->where('request_momTask.id',$id)
                        ->first();
        $getSubmissionData = DB::table('request_mom')->where('id', $getMomID->req_id)->first();
        $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator

        $mailData = [
            "all" => 1,
            "action_id" => 0,
            "submission" => $getSubmissionData,
            "email" => $getCreator->email, // kirim kepada creator
            "fullname" => $getCreator->fullname,
            "message" => $this->mailMessage()['newActivity'],
            "remarks" => $mode,
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

            // return $uniqueReminders;

            foreach($uniqueReminders as $g) {

                // $getMomID = DB::table('request_momTask')->select('tbl_category.req_id')
                //                 ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
                //                 ->where('request_momTask.id',$g)
                //                 ->first();
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
                ];

                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
            }
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
}
