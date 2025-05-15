<?php

namespace App\Http\Controllers\Submission;

use DB;
use COM;
use Log;
use App\Models\User;
use App\Models\Module;

use App\Models\Attachment;
use App\Models\Useraccess;
use App\Mail\SubmissionMail;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\MemorandumHis;
use Illuminate\Support\Carbon;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\MemorandumReq;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;

class MemoController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new MemorandumReq();
        $this->modulename = 'MemorandumReq';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }


    // public function index()
    // {
    //     $xpc = DB::table('employee.tbl_employee')
    //     ->where(function($query) {
    //         $query->where('contract_status', 'contract')
    //               ->orWhere(function($subQuery) {
    //                   $subQuery->where('contract_status', 'permanent')
    //                            ->whereRaw('DATEDIFF(YEAR, birthofdate, GETDATE()) >= 55');
    //               });
    //     })
    //     ->get();


    //     // $xpc = DB::table('memoView')
    //     // ->join('codes', 'memoView.id', '=', 'codes.id')
    //     // ->select('memoView.*',
    //     // 'codes.code') 
    //     // ->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $xpc
    //     ]);

    // }
    public function index()
    {
        try {
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end 
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id 
                where l.ApprovalAction='1' 
                and l.req_id = request_memorandum.id and l.module_id = '".$module_id."' 
                and request_memorandum.requestStatus='1'
                order by a.sequence)"; 

            $getAssignment = "(select top 1
            CASE WHEN user_id='".$user_id."' then 1 else 0 end
            from
            (select
            u.id as user_id,
            u.fullname as nama_users
            from tbl_assignment l
            left join employee.tbl_employee e on l.employee_id = e.id
            left join users u on e.LoginName = u.username
            where l.req_id = request_memorandum.id
            and l.module_id = '".$module_id."') as tab1
            where user_id = '".$user_id."')";

            $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
            $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;      
            $data = $dataquery
                ->selectRaw("request_memorandum.id,
                    request_memorandum.user_id,
                    request_memorandum.requestStatus,   
                    request_memorandum.employee_id,
                    request_memorandum.created_at,
                    request_memorandum.bu, 
                    request_memorandum.sysid,  
                    employee.tbl_employee.FullName,
                    employee.tbl_employee.SAPID,
                    employee.tbl_employee.sys_id,
                    employee.tbl_employee.BirthOfDate, 
                    employee.tbl_level.Level,
                    employee.tbl_designation.DesignationName,
                    codes.code,
                    CASE WHEN request_memorandum.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_memorandum.code_id','codes.id')
                // ->leftJoin('request_memorandum_his','request_memorandum.sysid','request_memorandum_his.sysid')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->leftJoin('tbl_assignment', function($join) use ($module_id) {
                    $join->on('request_memorandum.id', '=', 'tbl_assignment.req_id')
                        ->where('tbl_assignment.module_id', '=', $module_id);
                })
                ->leftJoin('employee.tbl_employee as emp', 'tbl_assignment.employee_id', '=', 'emp.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $getAllview) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $getAllview) {
                            if ($isAdmin) {
                                $query->whereIn("request_memorandum.requestStatus", [1,3,4])
                                    ->where("request_memorandum.user_id", "!=", $user_id);
                            } else if($getAllview) {
                                $query->whereIn("request_memorandum.requestStatus", [3])
                                ->where("request_memorandum.user_id", "!=", $user_id);
                            } else {
                                $query->where("request_memorandum.user_id", "!=", $user_id)
                                ->whereIn("request_memorandum.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_memorandum.user_id", $user_id);
                })
                ->where(function ($query) use ($user_id,$getAssignment, $isAdmin, $getAllview, $subquery) {
                    if(!$isAdmin) {
                        if(!$getAllview) {
                            $query->whereRaw($getAssignment . " = 1")
                            ->orWhere("request_memorandum.user_id", $user_id)
                            ->orWhereRaw($subquery . " = 1");
                        }
                    }
                })
                ->groupBy('request_memorandum.id',
                    'request_memorandum.user_id',
                    'request_memorandum.requestStatus',
                    'request_memorandum.created_at',
                    'request_memorandum.employee_id',
                    'request_memorandum.bu',
                    'request_memorandum.sysid',
                    'codes.code',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.SAPID',
                    'employee.tbl_employee.sys_id',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                    'employee.tbl_employee.deptheadName',
                )                
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_memorandum.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_memorandum.created_at desc")
                ->get();
                // Ambil Memorandum Histories untuk TreeList
        $sysids = $data->pluck('sysid')->unique()->toArray();
        $memorandumHistories = DB::table('request_memorandum_his')
            ->whereIn('sysid', $sysids)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('sysid');

        // Bentuk Data dalam Format Hierarki untuk DevExtreme TreeList
        $formattedData = [];
        foreach ($data as $row) {
            $children = $memorandumHistories->get($row->sysid, collect())->map(function ($history) {
                return [
                    'id' => $history->id,
                    'parent_id' => $history->sysid,
                    'req_id' => $history->req_id,
                    'sequence' => $history->sequence,
                    'startContract' => $history->startContract,
                    'endContract' => $history->endContract,
                    'approveddoc' => $history->approveddoc,
                    'remarks' => $history->remarks,
                    'user_id' => $history->user_id,
                    'sysid' => $history->sysid,
                    'requestStatus' => $history->requestStatus,
                    'created_at' => $history->created_at,                    
                ];
            });

            $formattedData[] = [
                'id' => $row->id,
                'parent_id' => $row->sysid,
                'FullName' => $row->FullName,
                'SAPID' => $row->SAPID,
                'BirthOfDate' => $row->BirthOfDate,
                'Level' => $row->Level,
                'DesignationName' => $row->DesignationName,
                'code' => $row->code,
                'user_id' => $row->user_id,
                'requestStatus' => $row->requestStatus,   
                'employee_id' => $row->employee_id,
                'created_at' => $row->created_at,
                'bu' => $row->bu,
                'children' => $children->values()
            ];
        }
           
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $formattedData
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {

        // DB::beginTransaction();

        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            // $requestData['user_id'] = $this->getAuth()->id;
            $requestData['sysid'] = $this->getEmployeeID()->sys_id;
            $requestData['employee_id'] = $this->getEmployeeID()->id;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);
            // dd($newData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $this->createApprManager($requestData['depthead_id'], $this->modulename, $req_id);

            // DB::commit();

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
        $user_id = $this->getAuth()->id;
        $module_id = $this->getModuleId($this->modulename);

        $subquery = "(select TOP 1 CASE WHEN a.user_id='" . $user_id . "'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id
            where l.ApprovalAction='1' and l.req_id = request_memorandum.id and l.module_id = '" . $module_id . "' and request_memorandum.requestStatus='1'
            order by a.sequence)";

        $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
        $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;

        try {
            // Ambil data request_memorandum
            $data = $this->model
                ->select(
                    'request_memorandum.*',
                    'codes.code',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.SAPID', 
                    'employee.tbl_employee.sys_id', 
                    'employee.tbl_employee.JoinDate', 
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_employee.contract_status',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_employee.JoinDate',
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                )
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->where('request_memorandum.id', $id)
                ->first();

            if (!$data) {
                return response()->json(["status" => "error", "message" => "Data tidak ditemukan"], 404);
            }

            // Jika code_id null, maka generate kode baru
            if ($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }

            // Tambahkan atribut ismine
            $data->ismine = ($data->employee_id == $user_id);

            // Tambahkan atribut ispendingonme
            $data->isPendingOnMe = $this->model
                ->selectRaw($subquery . " as isPendingOnMe")
                ->where('id', $id)
                ->first()
                ->isPendingOnMe;        

                $sysid = $data->sysid;
                $memorandumHistories = DB::table('request_memorandum_his')
                    ->where('sysid', $sysid)
                    ->get();                
                $data->memorandumHistories = $memorandumHistories;
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data, // Data utama request_memorandum
                // 'memorandumHistories' => $memorandumHistories // Semua data dari request_memorandum_his
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        
        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $data->update($requestData);
            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error", 
                "message" => $e->getMessage()]);
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