<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Models\Submission\Hrsc;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Assignmentto;
use DB;
use App\Mail\SubmissionMail;

class HrscRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Hrsc();
        $this->modulename = 'Hrsc';
        $this->module = new Module();
    }

    public function index(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employeeid = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;
            $isDeveloper = $this->isDeveloper();

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."' then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.ApprovalAction='1' and l.req_id = request_hrsc.id and l.module_id = '".$module_id."' and request_hrsc.requestStatus='1'
            order by a.sequence)";

            if(!$isAdmin) {
                $dataquery->selectRaw("CASE WHEN tbl_assignment.employee_id = '".$employeeid."' then 1 else 0 end as isPIC");
                $dataquery->leftJoin('tbl_assignment',function($join) use ($module_id){
                    $join->on('request_hrsc.id','=','tbl_assignment.req_id')
                         ->where('tbl_assignment.module_id',$module_id);
                });
            }

            $data = $dataquery
                ->selectRaw("request_hrsc.*,codes.code,
                    CASE WHEN request_hrsc.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_hrsc.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $isDeveloper, $employeeid, $module_id) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $isDeveloper, $employeeid, $module_id) {
                            if ($isAdmin) {
                                $query->where("request_hrsc.user_id", "!=", $user_id)
                                    ->whereIn("request_hrsc.requestStatus", [1,3,4]);
                            } else {
                                $query->where("tbl_assignment.employee_id",$employeeid)
                                    ->whereIn("request_hrsc.requestStatus", [3]);
                            }
                        })             
                        ->orWhere("request_hrsc.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_hrsc.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_hrsc.created_at desc")
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
        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['requestStatus'] = 0;

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $this->createApproverList($this->modulename, $req_id);
            
            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {

            $data = $this->model->select('request_hrsc.*','codes.code')
            ->leftJoin('codes','request_hrsc.code_id','codes.id')
            ->where('request_hrsc.id',$id)
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

            // Mengambil semua data dari request

            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();
            
            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            // $requestData['confirmationStatus'] = null;
            
            if($data->ticketStatus == null) {
                $requestData['ticketStatus'] = 'On Queue';
                $requestData['confirmationStatus'] = null;
            } else {
                if($request->confirmationStatus !== null) {
                    $requestData['confirmationStatus'] = $request->confirmationStatus;
                    $requestData['ticketStatus'] = $data->ticketStatus;
                } else {
                    $requestData['ticketStatus'] = $request->ticketStatus;
                    $requestData['confirmationStatus'] = null;
                }
            }
            ($data->ticketStatus == 'On Queue' || $data->ticketStatus == 'Immediately') ? $requestData['confirmationStatus'] = 'Waiting' : $requestData['confirmationStatus'];

            $ticketStatus = (isset($requestData['ticketStatus'])) ? $requestData['ticketStatus'] : $data->ticketStatus;
            $confirmationStatus = (isset($requestData['confirmationStatus'])) ? $requestData['confirmationStatus'] : $data->confirmationStatus;

            //start save history perubahan
            $fields = [
                'ticketStatus' => $request->ticketStatus,
                'confirmationStatus' => ($data->ticketStatus == 'Completed' && ($request->confirmationStatus != 'Waiting')) ? $request->confirmationStatus .' - '. $request->confirmationRemarks : null,
            ];
            
            foreach ($fields as $key => $value) {
                if ($value) {
                    $this->approverAction($this->modulename, $id, $key, 1, $value);
                }
            }
            //end save history perubahan

            $data->update($requestData);
            $notificationMessage = $this->generateNotificationMessage($data, $this->modulename, $id, $ticketStatus, $confirmationStatus);

            if($request->confirmationStatus != 'Waiting') {
                $newData['confirmationRemarks'] = null;
                $data->update($newData);
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

    function generateNotificationMessage($data, $modulename, $id, $ticketStatus, $confirmationStatus) {
        $locModel = "App\Models\Submission\\".$modulename;
        $model = new $locModel;
        $tableName = $model->getTableName();
        $module_id = $this->getModuleId($modulename);

        $getSubmissionData = DB::table($tableName)->where('id', $id)->first();
        $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator
        $assignmentdata = Assignmentto::leftJoin('tbl_employee','tbl_assignment.employee_id','=','tbl_employee.id')
                        ->leftJoin('users','tbl_employee.LoginName','=','users.username')
                        ->select('tbl_employee.*','users.email')
                        ->where('req_id',$getSubmissionData->id)
                        ->where('module_id',$module_id)
                        ->get();

        if ($ticketStatus === 'Completed') {
            $mailData = [
                "id" => 5, //notif status
                "action_id" => 0,
                "submission" => $getSubmissionData,
                "email" => $getCreator->email,
                "fullname" => $getCreator->fullname,
                "message" => $this->mailMessage()['hrscTicketCompleted'],
            ]; // send to creator
            Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
        }
        if ($confirmationStatus === 'Completed') {
            foreach ($assignmentdata as $getPIC){
                $mailData = [
                    "id" => 5, //notif status
                    "action_id" => 0,
                    "submission" => $getSubmissionData,
                    "email" => $getPIC->email,
                    "fullname" => $getPIC->FullName,
                    "message" => $this->mailMessage()['hrscConfirmStatusCompleted'],
                ]; // send to PIC
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
            }
        }
        if ($confirmationStatus === 'Reworked') {
            foreach ($assignmentdata as $getPIC){
                $mailData = [
                    "id" => 5, //notif status
                    "action_id" => 0,
                    "submission" => $getSubmissionData,
                    "email" => $getPIC->email,
                    "fullname" => $getPIC->FullName,
                    "message" => $this->mailMessage()['hrscConfirmStatusReworked'],
                ]; // send to PIC
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
            }
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
                    $attachments = Attachment::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->get();
                    Attachment::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                        foreach ($attachments as $attachment) {
                            unlink($this->copyuploadpath() .$attachment->path);
                        }
                    Assignmentto::where('req_id', $id)
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
}
