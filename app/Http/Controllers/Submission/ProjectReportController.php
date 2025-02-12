<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Project;
use App\Models\Module;
use App\Http\Traits\ProcessProjectTrait;
use DB;

class ProjectReportController extends Controller
{
    use ProcessProjectTrait;
    
    public $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Project();
        $this->modulename = 'Project';
        $this->module = new Module();
    }
    
    public function index(Request $request)
    {

        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $requestProjectData = DB::table('request_project')
            ->select('*')
            ->get();

            $dataquery = $this->model->query();
            
            $data = $dataquery
                ->selectRaw("request_project.id,
                codes.code,
                users.username AS requester_name,
                (SELECT STRING_AGG(reference.tbl_developer.developerName, ', ' )
                FROM tbl_assignment
                LEFT JOIN reference.tbl_developer ON reference.tbl_developer.id = tbl_assignment.developer_id
                WHERE tbl_assignment.req_id = request_project.id) AS developer_name,               

                (SELECT STRING_AGG(users.fullname, ', ')
                FROM tbl_approverListReq
                LEFT JOIN tbl_approver ON tbl_approverListReq.approver_id = tbl_approver.id
                LEFT JOIN users ON tbl_approver.user_id = users.id
                AND tbl_approverListReq.req_id = request_project.id
                ) AS approver_name,

                 (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_project.id 
                AND tbl_approverListHistory.approvalType = 'Originator')
                AS Originator,                

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_project.id 
                AND tbl_approverListHistory.approvalType = 'Submitted')
                AS Submitted,                

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_project.id 
                AND tbl_approverListHistory.approvalType = 'Approver')
                AS Approver,   

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_project.id 
                AND tbl_approverListHistory.approvalType = 'projectStatus')
                AS ps,        

                (SELECT TOP 1 tbl_approverListHistory.approvalDate 
                FROM tbl_approverListHistory 
                WHERE tbl_approverListHistory.req_id = request_project.id 
                AND tbl_approverListHistory.approvalType = 'progress')
                AS progres
                 ")       
                ->leftjoin('users', 'request_project.user_id', '=', 'users.id')          
                ->leftJoin('tbl_assignment', 'tbl_assignment.req_id', '=','request_project.id')
                ->leftJoin('reference.tbl_developer', 'reference.tbl_developer.id','=', 'tbl_assignment.developer_id')
                ->leftJoin('codes','request_project.code_id','codes.id')
                ->where('requestStatus', 3)
                ->where('isParent', 0)
                ->groupBy('users.username', 'request_project.id', 'codes.code', 'request_project.user_id')
                ->orderByDesc("codes.code")
                ->with(['user'])
                ->get();

                foreach ($data as $item) {
                    $requestData = $requestProjectData->firstWhere('id', $item->id);

                    if ($requestData) {
                        foreach ($requestData as $key => $value) {
                            $item->$key = $value;                            
                        }
                    }
                }

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show($id)
    {
        //
    }

    
    public function edit($id)
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
