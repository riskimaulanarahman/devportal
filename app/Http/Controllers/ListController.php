<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\User;
use App\Models\Employee;
use App\Models\Icon;
use App\Models\SideMenu;
use App\Models\Sequence;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Ethnic;
use App\Models\Religion;
use App\Models\Nationality;
use App\Models\Code;
use App\Models\Approvaltype;
use App\Models\Developer;
use App\Models\Approvaluser;
use App\Models\Submission\Project;
use Auth;

class ListController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function listUser() {
        return User::select('id','fullname')->get();
    }

    public function listModule() {
        return Module::select('id','module')->get();
    }

    public function listEmployee() {
        return Employee::select('tbl_employee.id', 'sapid', 'fullname', 'companycode', 'tbl_department.departmentname', 'tbl_department.departmentgroup')
                ->leftJoin('tbl_department', 'tbl_employee.department_id', '=', 'tbl_department.id')
                ->get();
    }

    public function listEmployeeSameDept() {

        $departmentGroup = $this->getEmployeeID()->department->DepartmentGroup;
        $loginName = $this->getEmployeeID()->LoginName;

        $employee = Employee::query()
                ->select('tbl_employee.id', 'sapid', 'fullname', 'companycode', 'tbl_department.departmentname', 'tbl_department.departmentgroup')
                ->leftJoin('tbl_department', 'tbl_employee.department_id', '=', 'tbl_department.id')
                ->where(function ($query) use ($departmentGroup) {
                    $query->whereNull('tbl_employee.loginname')
                        ->orWhere('tbl_employee.loginname', '')
                        ->where('tbl_department.departmentgroup', $departmentGroup);
                })
                ->orWhere('tbl_employee.loginname', $loginName)
                ->get();

        $employee->makeHidden(['department', 'designation']);

        return $employee;

    }
    
    public function listSideMenu() {
        return SideMenu::select('id','title')->where('is_parent',1)->get();
    }
    
    public function listIcon() {
        return Icon::select('id','name')->get();
    }

    public function listSequence() {
        return Sequence::select('id','title')->get();
    }

    public function listCompany() {
        return Company::select('id','companycode')->where('isUsed',1)->orderBy('companycode','asc')->get();
    }

    public function listDepartment() {
        return Department::select('*')->orderBy('departmentname','asc')->get();
    }

    public function listLocation() {
        return Location::select('id','location')->orderBy('location','asc')->get();
    }

    public function listDesignation() {
        return Designation::select('*')->orderBy('designationname','asc')->get();
    }

    public function listGrade() {
        return Grade::select('id','grade')->orderBy('grade','asc')->get();
    }

    public function listLevel() {
        return Level::select('id','level')->orderBy('level','asc')->get();
    }

    public function listEthnic() {
        return Ethnic::select('id','ethnicname')->orderBy('ethnicname','asc')->get();
    }

    public function listReligion() {
        return Religion::select('id','religionname')->orderBy('religionname','asc')->get();
    }

    public function listNationality() {
        return Nationality::select('id','nationalityname')->orderBy('nationalityname','asc')->get();
    }

    public function listCode() {
        return Code::select('id','code')->get();
    }

    public function listApprovaltype() {
        $data = Approvaltype::select('id','Module','ApprovalType')->get();

        return response()->json([
            "status" => "show",
            "message" => $this->getMessage()['show'],
            "items" => $data
        ]);

    }

    public function listDeveloper() {
        return Developer::select('id','developerName')->orderBy('developerName','asc')->get();
    }

    public function listApprover($modulename) {
        return Approvaluser::select('tbl_approver.id','users.fullname','tbl_approvaltype.ApprovalType')
        ->leftJoin('users','tbl_approver.user_id','users.id')
        ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
        ->where('tbl_approver.module',$modulename)
        ->get();
    }

    public function listProject() {
        $progress = Project::select('users.fullname','nameSystem','progress')
                ->leftJoin('users','request_project.user_id','users.id')
                ->where('projectStatus','Progress')
                ->get();
        
        $data = [
            "progress" => $progress
        ];

        Session::put('project', $data);

        return redirect()->route('login');

        // return response()->json($data);

    }

}
