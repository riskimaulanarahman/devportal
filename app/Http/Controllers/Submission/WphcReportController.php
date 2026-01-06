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
    try {
        $user_id   = $this->getAuth()->id;
        $user      = auth()->user();
        $module_id = $this->getModuleId($this->modulename);

        // Subquery untuk cek akses view
        $getAccess = "(
            SELECT CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM [authorization].tbl_useraccess l 
                    WHERE l.module_id = '".$module_id."'
                      AND l.allowView = '1'
                      AND l.employee_id = '".$user_id."'
                ) THEN 1 ELSE 0 
            END
        )";

        $data = DB::table('request_wphc_detail as d')
            ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
            ->join('employee.tbl_employee as e', 'm.employee_id', '=', 'e.id')
            ->leftJoin('employee.tbl_department as dept', 'e.department_id', '=', 'dept.id')
            ->leftJoin('employee.tbl_designation as pos', 'e.designation_id', '=', 'pos.id')
            ->leftJoin('employee.tbl_employee as sup', 'm.Superior', '=', 'sup.id')
            ->leftJoin('employee.tbl_employee as head', 'm.DeptHead', '=', 'head.id')
            ->selectRaw("
                d.startDate as WorkDate,
                d.id,
                m.user_id,
                (SELECT TOP 1 r.approvalDate
                 FROM tbl_approverListReq r
                 JOIN reference.tbl_module mod ON r.module_id = mod.id
                 WHERE r.req_id = m.id
                   AND mod.module = 'Wphc'
                   AND r.approvalDate IS NOT NULL
                 ORDER BY r.approvalDate DESC) as FullApprovedDate,
                e.SAPID,
                e.FullName as Name,
                dept.DepartmentName as Department,
                pos.DesignationName as Position,
                m.BU as BusinessGroup,
                sup.FullName as SuperiorName,
                head.FullName as DeptHeadName,
                ".$getAccess." as isMine,
                (
                    SELECT TOP 1 CASE WHEN a.user_id = '".$user_id."' THEN 1 ELSE 0 END
                    FROM tbl_approverListReq l
                    LEFT JOIN tbl_approver a ON l.approver_id = a.id
                    LEFT JOIN tbl_approvaltype t ON a.approvaltype_id = t.id 
                    WHERE l.ApprovalAction = '1' 
                      AND l.req_id = m.id 
                      AND l.module_id = '".$module_id."' 
                    ORDER BY a.sequence
                ) AS isPendingOnMe
            ")
            ->where('m.requestStatus', 3)
            ->where('d.isApproved', 1)
            ->orderBy('d.startDate', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status'  => "empty",
                'message' => "Data tidak ditemukan",
                'data'    => []
            ]);
        }

        return response()->json([
            "status"  => "show",
            "message" => "Seluruh data WPHC berhasil ditampilkan untuk keperluan laporan",
            "data"    => $data
        ]);

    } catch (\Exception $e) {
        return response()->json([
            "status"  => "error",
            "message" => $e->getMessage()
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
