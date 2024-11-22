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
use App\Models\CategoryForm;
use App\Models\Submission\Project;
use App\Models\UavAsset;
use App\Models\Categoryhrsc;
use App\Models\Unit;
use App\Models\Currency;
use App\Models\Ecatalog;
use App\Models\Purchasinguser;
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

    public function listEmployee() { // not have account/loginName
        return Employee::select('employee.tbl_employee.id', 'sapid', 'fullname', 'companycode', 'employee.tbl_department.departmentname', 'employee.tbl_department.departmentgroup')
                ->leftJoin('employee.tbl_department', 'employee.tbl_employee.department_id', '=', 'employee.tbl_department.id')
                ->where(function($query) {
                    $query->whereNotNull('LoginName')
                        ->where('LoginName', '<>', '');
                })
                // ->where('employee.tbl_employee.isActive',1)
                ->get();
    }

    public function listEmployeeAll() {
        return Employee::selectRaw('employee.tbl_employee.sys_id ,employee.tbl_employee.id, sapid, fullname, companycode, employee.tbl_department.departmentname, employee.tbl_department.departmentgroup, employee.tbl_level.level as levels')
            ->leftJoin('employee.tbl_department', 'employee.tbl_employee.department_id', '=', 'employee.tbl_department.id')
            ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                // ->where('employee.tbl_employee.isActive',1)
                ->get();
    }

    public function listEmployeeAD() { // have account/loginName
        return Employee::select('employee.tbl_employee.id', 'sapid', 'fullname', 'companycode', 'employee.tbl_department.departmentname', 'employee.tbl_department.departmentgroup')
                ->leftJoin('employee.tbl_department', 'employee.tbl_employee.department_id', '=', 'employee.tbl_department.id')
                // ->where(function($query) {
                //     $query->whereNotNull('LoginName')
                //         ->where('LoginName', '<>', '');
                // })
                ->whereNotNull('LoginName')
                // ->where('employee.tbl_employee.isActive',1)
                ->get();
    }

    public function listEmployeeSameDept() {

        $departmentGroup = $this->getEmployeeID()->department->DepartmentGroup;
        $loginName = $this->getEmployeeID()->LoginName;

        $employee = Employee::query()
                ->select('employee.tbl_employee.id', 'sapid', 'fullname', 'companycode', 'employee.tbl_department.departmentname', 'employee.tbl_department.departmentgroup')
                ->leftJoin('employee.tbl_department', 'employee.tbl_employee.department_id', '=', 'employee.tbl_department.id')
                ->where(function ($query) use ($departmentGroup) {
                    $query->whereNull('employee.tbl_employee.loginname')
                        ->orWhere('employee.tbl_employee.loginname', '')
                        ->where('employee.tbl_department.departmentgroup', $departmentGroup);
                })
                ->orWhere('employee.tbl_employee.loginname', $loginName)
                // ->where('employee.tbl_employee.isActive',1)
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

    public function listCompanyCode() {
        return Company::select('id','CompanyCode')->where('isUsed',1)->get();
    }

    public function listCompany() {
        return Company::select('*')->where('isUsed',1)->orderBy('companycode','asc')->get();
    }

    public function listDepartment() {
        return Department::select('*')->orderBy('departmentname','asc')->where('isUsed',1)->get();
    }

    public function listLocation() {
        return Location::select('*')->orderBy('location','asc')->where('isUsed',1)->get();
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
        return Developer::select('id','developerName')->whereNotNull('role')->orderBy('developerName','asc')->get();
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

    public function listParentProject() {
        return Project::where('requestStatus',3)->where('projectStatus','Completed')->get();
    }

    public function listCategoryFormWithModule($modulename) {
        return CategoryForm::with('module')->select('*')->where('module_id',$this->getModuleId($modulename))->get();
    }

    public function listCategoryForm() {
        return CategoryForm::with('module')->select('*')->get();
    }

    public function listUavAsset() {
        return UavAsset::select('id','bu','sector','tools','brand','listName')->get();
    }

    public function listCategoryHrsc() {
        return Categoryhrsc::all();
    }

    public function listUnit() {
        return Unit::select('*')->orderBy('nama','asc')->get();
    }

    public function listCurrency() {
        return Currency::select('*')->orderBy('nama','asc')->get();
    }

    public function listBuyer() { // MMF
        return Approvaluser::selectRaw('employee.tbl_employee.id,tbl_approver.id as approver_id,users.fullname,tbl_approvaltype.ApprovalType')
        ->leftJoin('users','tbl_approver.user_id','users.id')
        ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
        ->leftJoin('employee.tbl_employee','tbl_approver.employee_id','employee.tbl_employee.id')
        ->where('tbl_approver.module','Mmf')
        ->where('tbl_approvaltype.ApprovalType','Buyer') // Buyer
        ->get();
    }

    public function listEcatalog() {
        return Ecatalog::select('*')->orderBy('description','asc')->get();
    }

    public function listPurchasinguser() {
        return Purchasinguser::select('*')->with('employee')->get();
    }
}
