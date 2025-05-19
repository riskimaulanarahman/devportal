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
                and l.req_id = request_memorandum_his.id and l.module_id = '".$module_id."' 
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
            where l.req_id = request_memorandum_his.id
            and l.module_id = '".$module_id."') as tab1
            where user_id = '".$user_id."')";

            $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
            $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;      
            $data = $dataquery
                ->selectRaw("request_memorandum_his.id,
                    request_memorandum.user_id,
                    request_memorandum.requestStatus,   
                    request_memorandum.employee_id,
                    request_memorandum.created_at,
                    request_memorandum.bu,
                    request_memorandum.sysid,  
                    request_memorandum_his.sequence,  
                    request_memorandum_his.req_id,  
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
                ->leftJoin('request_memorandum_his','request_memorandum.id','request_memorandum_his.req_id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->leftJoin('tbl_assignment', function($join) use ($module_id) {
                    $join->on('request_memorandum_his.id', '=', 'tbl_assignment.req_id')
                        ->where('tbl_assignment.module_id', '=', $module_id);
                })
                ->leftJoin('employee.tbl_employee as emp', 'tbl_assignment.employee_id', '=', 'emp.id')
                // ->with(['user','approverlist'])
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
                ->groupBy('request_memorandum_his.id',
                    'request_memorandum.user_id',
                    'request_memorandum.requestStatus',
                    'request_memorandum.created_at',
                    'request_memorandum.employee_id',
                    'request_memorandum.bu',
                    'request_memorandum_his.sequence',
                    'request_memorandum_his.req_id',
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
                ->orderByRaw("CASE WHEN request_memorandum.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_memorandum.created_at asc")
                ->get();
                $parents = [];                
                foreach ($data as $item) {
                    if ($item->sequence == 1) {
                        $parents[$item->sysid] = $item->id;
                    }
                    if ($item->id === null) {
                        continue;
                    }
                }
                $mappedData = [];
                // dd($data);
                foreach ($data as $item) {
                    $mappedData[] = [
                        'id' => $item->id, 
                        'isParent' => ($item->sequence == 1) ? 1 : 0, 
                        'parentID' => ($item->sequence == 1) ? null : ($parents[$item->sysid] ?? null), // Ambil parent berdasarkan sysid jika ada
                        'user_id' => $item->user_id,
                        'requestStatus' => $item->requestStatus,
                        'created_at' => $item->created_at,
                        'bu' => $item->bu,
                        'employee_id' => $item->employee_id,
                        'sysid' => $item->sysid,
                        'sequence' => $item->sequence,
                        'FullName' => $item->FullName,
                        'SAPID' => $item->SAPID,
                        'sys_id' => $item->sys_id,
                        'BirthOfDate' => $item->BirthOfDate,
                        'Level' => $item->Level,
                        'deptheadName' => $item->deptheadName,
                        'DesignationName' => $item->DesignationName,
                        'code' => $item->code,
                        'isMine' => $item->isMine,
                        'isPendingOnMe' => $item->isPendingOnMe
                    ];
                }
                // dd($mappedData);
           
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $mappedData
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
                    'request_memorandum_his.id',
                    'employee.tbl_employee.sys_id',
                    'employee.tbl_employee.SAPID', 
                    'employee.tbl_employee.JoinDate', 
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_employee.contract_status',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_employee.JoinDate',
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_employee.companycode',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                )
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('request_memorandum_his', 'request_memorandum.id', '=', 'request_memorandum_his.req_id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->where('request_memorandum_his.id', $id)
                ->first();

            if (!$data) {
                return response()->json(["status" => "error", "message" => "Data tidak ditemukan"], 404);
            }

            // Jika code_id null, maka generate kode baru
            if ($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }
            // Ambil sysid dan sequence secara dinamis berdasarkan reqid yang dipilih
            $historyData = DB::table('request_memorandum_his')
                ->select('sysid', 'sequence')
                ->where('id', $id)
                ->first();

            if (!$historyData) {
                return response()->json(["status" => "error", "message" => "Data sejarah tidak ditemukan"], 404);
            }

            $selected_sequence = $historyData->sequence; // Sequence yang terkait dengan reqid
            $sysid = $historyData->sysid; // Sysid terkait

            // Ambil data memorandumHistories berdasarkan sequence dan sysid secara dinamis
            $memorandumHistories = DB::table('request_memorandum_his')
                ->selectRaw("
                    MIN(req_id) AS id, 
                    id AS hisid, 
                    sequence, 
                    startContract,
                    endContract,
                    approveddoc,
                    created_at,
                    remarks,
                    sysid
                ")
                ->where('sysid', $sysid)
                ->where('sequence', '<=', $selected_sequence)
                ->groupBy('id',
                 'sequence',
                 'endContract',
                 'startContract',
                 'approveddoc',
                 'created_at',
                 'remarks',
                  'sysid')
                ->orderBy('sequence', 'DESC')
                ->get();
            //mapping
            $mappedData = [
                'id' => $data->id,
                'code_id' => $data->code_id,
                'user_id' => $data->user_id,
                'requestStatus' => $data->requestStatus,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
                'companycode' => $data->companycode,
                'FullName' => $data->FullName,
                'sys_id' => $data->sys_id,
                'SAPID' => $data->SAPID,
                'JoinDate' => $data->JoinDate,
                'deptheadName' => $data->deptheadName,
                'contract_status' => $data->contract_status,
                'BirthOfDate' => $data->BirthOfDate,
                'Level' => $data->Level,
                'DesignationName' => $data->DesignationName,
                'code' => $data->code,
                // 'memorandumHistories' => DB::table('request_memorandum_his')->where('sysid', $data->sysid)->get()
                'memorandumHistories' => $memorandumHistories
            ];   
            // dd($mappedData);         
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $mappedData, // Data utama request_memorandum
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