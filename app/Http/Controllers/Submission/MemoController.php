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
use App\Models\Submission\Memorandum;

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
        $this->model = new Memorandum();
        $this->modulename = 'Memorandum';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        try {
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;

            // Ambil akses pengguna
            $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
            $getAllview = $checkUserAccess ? $checkUserAccess->allowView : null;

            // Subquery untuk pengecekan apakah user memiliki pending approval
            $subquery = "(SELECT TOP 1 
                CASE WHEN a.user_id = '" . $user_id . "' THEN 1 ELSE 0 END 
                FROM tbl_approverListReq l
                LEFT JOIN tbl_approver a ON l.approver_id = a.id
                WHERE l.ApprovalAction = '1' 
                    AND l.req_id = request_memorandum.id 
                    AND l.module_id = '" . $module_id . "' 
                    AND request_memorandum.requestStatus = '1'
                ORDER BY a.sequence)";

            // Subquery untuk mendapatkan assignment user
            $getAssignment = "(SELECT TOP 1 
                CASE WHEN user_id = '" . $user_id . "' THEN 1 ELSE 0 END
                FROM (
                    SELECT u.id AS user_id
                    FROM tbl_assignment l
                    LEFT JOIN employee.tbl_employee e ON l.employee_id = e.id
                    LEFT JOIN users u ON e.LoginName = u.username
                    WHERE l.req_id = request_memorandum.id
                    AND l.module_id = '" . $module_id . "'
                ) AS tab1
                WHERE user_id = '" . $user_id . "')";

            // Query utama
            $data = $this->model
                ->selectRaw("
                    request_memorandum.id,
                    request_memorandum.parentID,
                    request_memorandum.user_id,
                    request_memorandum.requestStatus,   
                    request_memorandum.employee_id,
                    request_memorandum.created_at,
                    request_memorandum.bu, 
                    request_memorandum.sysid, 
                    request_memorandum.sequence, 
                    employee.tbl_employee.FullName,
                    employee.tbl_employee.sys_id,
                    employee.tbl_employee.BirthOfDate, 
                    employee.tbl_employee.JoinDate, 
                    employee.tbl_employee.companycode, 
                    employee.tbl_level.Level,
                    employee.tbl_designation.DesignationName,
                    codes.code,
                    users.fullname,
                    CASE WHEN request_memorandum.user_id = '" . $user_id . "' THEN 1 ELSE 0 END AS isMine,
                    " . $subquery . " AS isPendingOnMe
                ")
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('users', 'request_memorandum.user_id', '=', 'users.id')
                // ->join('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $getAllview) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $getAllview) {
                            if ($isAdmin) {
                                $query->whereIn("request_memorandum.requestStatus", [1, 3, 4])
                                    ->where("request_memorandum.user_id", "!=", $user_id);
                            } else if ($getAllview) {
                                $query->whereIn("request_memorandum.requestStatus", [3])
                                    ->where("request_memorandum.user_id", "!=", $user_id);
                            } else {
                                $query->where("request_memorandum.user_id", "!=", $user_id)
                                    ->whereIn("request_memorandum.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_memorandum.user_id", $user_id);
                })
                ->where(function ($query) use ($user_id, $getAssignment, $isAdmin, $getAllview, $subquery) {
                    if (!$isAdmin) {
                        if (!$getAllview) {
                            $query->whereRaw($getAssignment . " = 1")
                                ->orWhere("request_memorandum.user_id", $user_id)
                                ->orWhereRaw($subquery . " = 1");
                        }
                    }
                })
                ->groupBy([
                    'request_memorandum.id',
                    'request_memorandum.parentID',
                    'request_memorandum.user_id',
                    'request_memorandum.requestStatus',
                    'request_memorandum.created_at',
                    'request_memorandum.employee_id',
                    'request_memorandum.bu',
                    'request_memorandum.sysid',
                    'request_memorandum.sequence',
                    'codes.code',
                    'users.fullname',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.SAPID',
                    'employee.tbl_employee.sys_id',
                    'employee.tbl_employee.companycode',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_employee.JoinDate',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName'
                ])
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_memorandum.user_id = '" . $user_id . "' THEN 0 ELSE 1 END, request_memorandum.created_at DESC")
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
        try {
            $requestData = $request->all();
            // $employeeData = DB::table('employee.tbl_employee')
            // ->select('sys_id')
            // ->where('id', $requestData['employee_id'])
            // ->first();
            // if ($employeeData) {
            //     $requestData['sysid'] = $employeeData->sys_id;
            // }
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);
            $requestData['code_id'] = $this->generateCode($this->modulename);
            // $employee = $this->getEmployeeID();
            // if ($employee->companycode === 'KPSI') {
            //     $employee->companycode = 'IHM';
            // }
            // $requestData['bu'] = $employee->companycode;
            $newData = $this->model->create($requestData);

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

        $subquery = "(SELECT TOP 1 CASE WHEN a.user_id = '" . $user_id . "' THEN 1 ELSE 0 END 
            FROM tbl_approverListReq l
            LEFT JOIN tbl_approver a ON l.approver_id = a.id
            WHERE l.ApprovalAction = '1' 
                AND l.req_id = request_memorandum.id 
                AND l.module_id = '" . $module_id . "' 
                AND request_memorandum.requestStatus = '1'
            ORDER BY a.sequence)";

        try {
            // Ambil data utama dari request_memorandum berdasarkan ID
            $data = $this->model
                ->select('request_memorandum.*',                
                'employee.tbl_level.Level',
                'employee.tbl_designation.DesignationName',
                'employee.tbl_employee.JoinDate',
                'employee.tbl_employee.companycode',
                'employee.tbl_employee.sys_id as sysid',
                'employee.tbl_employee.BirthOfDate')
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
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

            // Tambahkan atribut isMine
            $data->isMine = ($data->employee_id == $user_id);

            // Tambahkan atribut isPendingOnMe
            $data->isPendingOnMe = $this->model
                ->selectRaw($subquery . " as isPendingOnMe")
                ->where('id', $id)
                ->first()
                ->isPendingOnMe;

            // **Tambahkan kelompok kontrak berdasarkan sysid, parentID, dan sequence**
            $contractList = $this->model
                ->select('sysid', 'id AS memorandum_id', 'parentID', 'sequence')
                ->where('sysid', $data->sysid) // Ambil semua kontrak dengan sysid yang sama
                ->orderBy('sequence', 'ASC') // Urutkan berdasarkan sequence (Parent lebih dulu)
                ->get();

            // Tambahkan list kontrak ke data utama
            $data->contractList = $contractList;

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
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

            // **Ambil sysid dari database jika tidak dikirim dalam request**
            if (!isset($requestData['sysid'])) {
                $requestData['sysid'] = $data->sysid;
            }

            // **Ambil sysid dan companycode berdasarkan employee_id jika employee_id diperbarui**
            if (isset($requestData['employee_id'])) {
                $employeeData = DB::table('employee.tbl_employee')
                    ->select('sys_id', 'companycode')
                    ->where('id', $requestData['employee_id'])
                    ->first();

                if ($employeeData) {
                    $requestData['sysid'] = $employeeData->sys_id;
                    $requestData['bu'] = $employeeData->companycode;
                }
            }

            // **Cari Parent berdasarkan sysid yang sama dengan sequence = 1**
            $parentData = $this->model
                ->select('id')
                ->where('sysid', $requestData['sysid'])
                ->where('sequence', 1)
                ->first();

            // **Tetapkan Sequence dan Parent ID**
            if (!isset($requestData['sequence'])) {
                $maxSequence = $this->model
                    ->where('sysid', $requestData['sysid'])
                    ->max('sequence');

                $requestData['sequence'] = $maxSequence ? $maxSequence + 1 : 1;
            }

            $requestData['parentID'] = ($requestData['sequence'] == 1) ? null : ($parentData ? $parentData->id : null);

            // **Pastikan tidak ada duplikasi sequence dalam sysid**
            $existingSequence = $this->model
                ->where('sysid', $requestData['sysid'])
                ->where('sequence', $requestData['sequence'])
                ->where('id', '!=', $id) // Pastikan bukan kontrak yang sedang diupdate
                ->exists();

            if ($existingSequence) {
                return response()->json([
                    "status" => "error",
                    "message" => "Sequence sudah digunakan dalam sysid yang sama!"
                ]);
            }

            // **Update data**
            $data->update($requestData);

            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
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