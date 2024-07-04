<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Jdi;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class JdiReportController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Jdi();
        $this->modulename = 'Jdi';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        try {
            
            $dataquery = $this->model->query();

            $data = $dataquery
                ->selectRaw("request_jdi.*,
                    codes.code,
                    dept.DepartmentName as department,
                    emp.SAPID as sapid,
                    lvl.Level as level,
                    emp1.FullName as anggota1,
                    lvl1.Level as anggota1level,
                    emp2.FullName as anggota2,
                    lvl2.Level as anggota2level,
                    RIGHT(request_jdi.noRegistration, 4) as year,
                    SUBSTRING(request_jdi.noRegistration, LEN(request_jdi.noRegistration) - 6, 2) as month
                ")
                ->leftJoin('codes','request_jdi.code_id','codes.id')
                ->leftJoin('employee.tbl_department as dept','request_jdi.department_id','dept.id')
                ->leftJoin('employee.tbl_employee as emp','request_jdi.pencetuside_id','emp.id')
                ->leftJoin('employee.tbl_level as lvl','emp.level_id','lvl.id')
                ->leftJoin('employee.tbl_employee as emp1','request_jdi.anggota1_id','emp1.id') //anggota 1
                ->leftJoin('employee.tbl_level as lvl1','emp1.level_id','lvl1.id') //anggota 1
                ->leftJoin('employee.tbl_employee as emp2','request_jdi.anggota2_id','emp2.id') //anggota 2
                ->leftJoin('employee.tbl_level as lvl2','emp2.level_id','lvl2.id') //anggota 2
                ->with(['user'])
                ->where('request_jdi.requestStatus',3)
                ->orderByRaw("request_jdi.submitDate desc")
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
