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
                // dd($data);

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
}
