<?php

namespace App\Http\Controllers;
use DB;
use App\Models\User;
use App\Models\Module;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission\Memorandum;

class MemorandumReportController extends Controller
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
{
    try {
        $employees = [];
        $contracts = [];

        $requests = Memorandum::select('id', 'employee_id')
            ->with(['employee:id,FullName,companycode,BirthOfDate,sys_id,deptheadName'])
            ->whereIn('id', function ($query) {
                $query->select('req_id')
                    ->from('request_memorandum_detail')
                    ->groupBy('req_id');
            })
            ->get();

        foreach ($requests as $req) {
            $employee = $req->employee;
            $employeeId = $req->employee_id;

            // 🔹 Simpan data pegawai hanya sekali
            if (!isset($employees[$employeeId])) {
                $employees[$employeeId] = [
                    "ID"            => $employeeId,
                    "EmployeeName"  => $employee->FullName ?? "Unknown",
                    "BU"            => $employee->companycode ?? "Unknown",
                    "sys_id"        => $employee->sys_id ?? "-",
                    "BirthOfDate"   => $employee->BirthOfDate,
                    "deptheadName"   => $employee->deptheadName,
                ];
            }

            $details = DB::table("request_memorandum_detail")
                ->where("req_id", $req->id)
                ->orderBy("sequence")
                ->get();

            foreach ($details as $detail) {
                $contracts[] = [
                    "ID"             => "{$req->id}_{$detail->sequence}", // ID unik
                    "EmployeeID"     => $employeeId,
                    "ContractLabel"  => "Kontrak #{$detail->sequence}",
                    "StartDate"      => $detail->startContract,
                    "EndDate"        => $detail->endContract,
                    "Remarks"        => $detail->remarks ?? "",
                    "ApprovedDoc"    => $detail->approveddoc ?? ""
                ];
            }
        }

        return response()->json([
            "employees" => array_values($employees),                
            "contracts" => $contracts
        ]);

    } catch (\Exception $e) {
        return response()->json([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
