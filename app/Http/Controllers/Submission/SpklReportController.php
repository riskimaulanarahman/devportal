<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Spkl;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class SpklReportController extends Controller
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

    public function index()
{
    $data = DB::table('request_spkl_detail as d')
        ->join('request_spkl as m', 'd.req_id', '=', 'm.id')
        ->join('employee.tbl_employee as e', 'd.employee_id', '=', 'e.id')
        ->leftJoin('employee.tbl_department as dept', 'e.department_id', '=', 'dept.id')
        ->leftJoin('employee.tbl_designation as pos', 'e.designation_id', '=', 'pos.id')
        ->leftJoin('employee.tbl_employee as sup', 'm.employee_id', '=', 'sup.id')
        ->leftJoin('employee.tbl_employee as head', 'm.DeptHead', '=', 'head.id')
        ->select([
            'm.work_date as WorkDate',
            'd.*',
            DB::raw("(SELECT TOP 1 r.approvalDate
          FROM tbl_approverListReq r
          WHERE r.req_id = m.id
            AND r.approvalDate IS NOT NULL
          ORDER BY r.approvalDate DESC) as FullApprovedDate"),
            'e.SAPID',
            'e.FullName as Name',
            'dept.DepartmentName as Department',
            'pos.DesignationName as Position',
            'm.BU as BusinessGroup',
            'sup.FullName as SuperiorName',
            'head.FullName as DeptHeadName'
        ])
        ->where('m.requestStatus', 3)
        ->where('m.tms', 34)
        ->orderBy('m.work_date', 'desc')
        ->get();

    return response()->json([
        "status"  => "show",
        "message" => "Seluruh data SPKL berhasil ditampilkan untuk keperluan laporan",
        "data"    => $data
    ]);
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
