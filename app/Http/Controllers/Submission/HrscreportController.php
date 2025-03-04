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

class HrscReportController extends Controller
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
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $requestHrscData = DB::table('request_hrsc')
            ->select('*')
            ->get();

            $dataquery = $this->model->query();


            $data = $dataquery
                ->selectRaw("request_hrsc.id,  
                codes.code,
                request_hrsc.user_id,
                
                (SELECT STRING_AGG(emp.fullname, ', ' )
                FROM tbl_assignment AS assign
                LEFT JOIN archive._tbl_employee AS emp
                ON emp.id = assign.employee_id
                WHERE assign.req_id = request_hrsc.id) AS pic_name,

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_hrsc.id 
                AND tbl_approverListHistory.approvalType = 'Originator')
                AS Originator,                

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_hrsc.id 
                AND tbl_approverListHistory.approvalType = 'Submitted')
                AS Submitted,                

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_hrsc.id 
                AND tbl_approverListHistory.approvalType = 'Approver')
                AS Approver,                
                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_hrsc.id 
                AND tbl_approverListHistory.approvalType = 'ticketStatus')
                AS ts,                
                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_hrsc.id 
                AND tbl_approverListHistory.approvalType = 'confirmationStatus')
                AS cs
                "
                )        
                ->leftjoin('tbl_assignment', 'tbl_assignment.req_id', '=', 'request_hrsc.id')
                ->leftjoin('archive._tbl_employee', 'archive._tbl_employee.id', '=', 'tbl_assignment.employee_id')
                ->leftJoin('codes','request_hrsc.code_id','codes.id')        
                ->leftjoin('tbl_approverListHistory', 'tbl_approverListHistory.req_id', '=', 'request_hrsc.id') 
                ->where('requestStatus', 3)      
                // ->groupBy('request_hrsc.id', 'codes.code', 'tbl_approverListHistory.approvalDate','tbl_approverListHistory.approvalType','request_hrsc.requestStatus','request_hrsc.bu') 
                ->groupBy('request_hrsc.id', 'codes.code', 'request_hrsc.user_id') 
                ->orderByDesc("codes.code") 
                ->with(['user'])
                ->get();


                foreach ($data as $item) {
                    $requestData = $requestHrscData->firstWhere('id', $item->id);

                    if ($requestData) {
                        foreach ($requestData as $key => $value) {
                            $item->$key = $value;                            
                        }
                        try {
                        
                        if ($item->Submitted && $item->Approver) {
                            $Submitted = \Carbon\Carbon::parse($item->Submitted);
                            $Approver = \Carbon\Carbon::parse($item->Approver);
                            
                            $diffInSeconds = $Submitted->diffInSeconds($Approver);

                            $days = floor ($diffInSeconds / 86400);                           
                            $hours = floor($diffInSeconds / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $remaining_seconds = $diffInSeconds % 60;
                            
                            $item->submit_to_approve = 
                            str_pad($days, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($remaining_seconds, 2, '0', STR_PAD_LEFT);
                            
                        } else {
                            $item->submit_to_approve = null;
                        }

                        if ($item->Approver && $item->ts) {
                            $Approver = \Carbon\Carbon::parse($item->Approver);
                            $ts = \Carbon\Carbon::parse($item->ts);
                            
                            $diffInSeconds = $Approver->diffInSeconds($ts);

                            $days = floor ($diffInSeconds / 86400);                           
                            $hours = floor($diffInSeconds / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $remaining_seconds = $diffInSeconds % 60;
                            
                            $item->approve_to_ts = 
                            str_pad($days, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($remaining_seconds, 2, '0', STR_PAD_LEFT);
                        } else {
                            $item->approve_to_ts = null;
                        }

                        if ($item->Submitted && $item->cs) {
                            $Submitted = \Carbon\Carbon::parse($item->Submitted);
                            $cs = \Carbon\Carbon::parse($item->cs);
                            
                            $diffInSeconds = $Submitted->diffInSeconds($cs);

                            $days = floor ($diffInSeconds / 86400);                           
                            $hours = floor($diffInSeconds / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $remaining_seconds = $diffInSeconds % 60;
                            
                            $item->submitted_to_cs = 
                            str_pad($days, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . 
                            str_pad($remaining_seconds, 2, '0', STR_PAD_LEFT);
                        } else {
                            $item->submitted_to_cs = null;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error Calculating submit_to_approve for ID'. $item->id . ': '. $e->getMessage());
                        $item->submit_to_approve = null;
                        $item->approve_to_ts = null;
                        $item->submitted_to_cs = null;
                    }
                    }
                }

                return response()->json([
                    'status' => "show",
                    'message' => $this->getMessage()['show'],
                    'data' => $data
                ])->setEncodingOptions(JSON_NUMERIC_CHECK);
    
            } catch (\Exception $e) {
    
                return response()->json(["status" => "error", "message" => $e->getMessage()
            ]);
        }
    }
    

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}