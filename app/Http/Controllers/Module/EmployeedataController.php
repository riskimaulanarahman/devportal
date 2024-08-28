<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApproverNotification;
use DB;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;

class EmployeedataController extends Controller
{
    private $model;
    public $company;

    public function __construct()
    {
        $this->model = new Employee();
        $this->company = new Company();
    }

    public function index(Request $request)
    {
        try {

            $getcompany = $this->company->where('isUsed',1)->pluck('CompanyCode');

            $data = $this->model
                    ->where('isActive',1)
                    ->whereIn('companycode',$getcompany)
                    ->get(); // show index data by employee

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            // Check if SAPID already exists
            $sapidExists = $this->model->where('SAPID', $request->SAPID)->exists();

            if ($sapidExists) {
                return response()->json(["status" => "error", "message" => "The SAPID has already been taken."]);
            }

            $requestData = $request->all();
            $requestData['companycode'] = $this->getCompanyName($request->company_id);
            $requestData['sys_id_depthead'] = $this->getsysid($request->deptheadName);
            $requestData['sys_id_superior'] = $this->getsysid($request->superiorName);

            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {

            // Check if SAPID already exists
            $sapidExists = $this->model->where('SAPID', $request->SAPID)->exists();

            if ($sapidExists) {
                return response()->json(["status" => "error", "message" => "The SAPID has already been taken."]);
            }
            
            $requestData = $request->all();
            if(isset($request->company_id)) {
                $requestData['companycode'] = $this->getCompanyName($request->company_id);
            }
            if(isset($request->deptheadName)) {
                $requestData['sys_id_depthead'] = $this->getsysid($request->deptheadName);
                $requestData['sys_id_superior'] = $this->getsysid($request->superiorName);
            }

            $data = $this->model->findOrFail($id);
            $oldData = $data->toArray();

            if(isset($request->company_id) || isset($request->department_id) || isset($request->designation_id) || isset($request->location_id)) {
                $checkapprover = DB::table('erp.dbo.vw_CombinedApprovers')
                ->where('employee_id',$id)
                ->get(); // check approver exist
                if ($checkapprover->isNotEmpty()) {
                    // Send email notification
                    $emails = DB::table('reference.tbl_developer')
                    ->join('users', 'reference.tbl_developer.user_id', '=', 'users.id')
                    ->where('reference.tbl_developer.role', 'sys')
                    ->pluck('users.email')
                    ->toArray();

                    if (!empty($emails)) {
                        // Send email notification
                        $listApprover = $checkapprover->toArray();
                        $approverData = [];
                        // foreach ($requestData as $key => $value) {
                        //     if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                        //         $approverData[] = [
                        //             'field' => $key,
                        //             'old_value' => $oldData[$key],
                        //             'new_value' => $value
                        //         ];
                        //     }
                        // }
                        foreach ($requestData as $key => $value) {
                            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                                $oldValue = $oldData[$key];
                                $newValue = $value;
            
                                // Convert IDs to names
                                switch ($key) {
                                    case 'company_id':
                                        $oldValue = Company::find($oldValue)->CompanyCode ?? $oldValue;
                                        $newValue = Company::find($newValue)->CompanyCode ?? $newValue;
                                        break;
                                    case 'department_id':
                                        $oldValue = Department::find($oldValue)->DepartmentName ?? $oldValue;
                                        $newValue = Department::find($newValue)->DepartmentName ?? $newValue;
                                        break;
                                    case 'designation_id':
                                        $oldValue = Designation::find($oldValue)->DesignationName ?? $oldValue;
                                        $newValue = Designation::find($newValue)->DesignationName ?? $newValue;
                                        break;
                                    case 'location_id':
                                        $oldValue = Location::find($oldValue)->Location ?? $oldValue;
                                        $newValue = Location::find($newValue)->Location ?? $newValue;
                                        break;
                                }
            
                                $approverData[] = [
                                    'field' => $key,
                                    'old_value' => $oldValue,
                                    'new_value' => $newValue
                                ];
                            }
                        }
                        Mail::to($emails)->send(new ApproverNotification($approverData,$listApprover));
                    }
                }
            }

            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);
            $data->update([
                'isActive' => 0
            ]);

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

}
