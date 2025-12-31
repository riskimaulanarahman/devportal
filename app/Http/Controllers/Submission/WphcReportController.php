<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Wphc;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class WphcReportController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Wphc();
        $this->modulename = 'Wphc';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index()
    {
        $data = DB::table('request_wphc_detail as d')
            ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
            ->join('employee.tbl_employee as e', 'm.employee_id', '=', 'e.id')
            ->leftJoin('employee.tbl_department as dept', 'e.department_id', '=', 'dept.id')
            ->leftJoin('employee.tbl_designation as pos', 'e.designation_id', '=', 'pos.id')
            // join untuk Superior
            ->leftJoin('employee.tbl_employee as sup', 'm.Superior', '=', 'sup.id')
            // join untuk DeptHead
            ->leftJoin('employee.tbl_employee as head', 'm.DeptHead', '=', 'head.id')
            ->select([
                'd.startDate as WorkDate',
                'd.id',
                DB::raw("(SELECT TOP 1 r.approvalDate
                FROM tbl_approverListReq r
                JOIN reference.tbl_module mod ON r.module_id = mod.id
                WHERE r.req_id = m.id
                    AND mod.module = 'Wphc'
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
            ->Where('requestStatus', 3)
            ->orderBy('d.startDate', 'desc')
            ->get();
// dd($data);

        return response()->json([
            "status"  => "show",
            "message" => "Seluruh data WPHC berhasil ditampilkan untuk keperluan laporan",
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
