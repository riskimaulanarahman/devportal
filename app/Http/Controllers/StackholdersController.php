<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Module;
use App\Models\Stackholders;
use App\Models\Employee;
use App\Models\User;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Approvaltype;
use Auth;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class StackholdersController extends Controller
{

    private $model;
    public $module;
    public $employee;
    public $user;

    public function __construct()
    {
        $this->model = new Stackholders();
        $this->module = new Module();
        $this->employee = new Employee();
        $this->user = new User();
    }

    public function index()
    {
        try {

            $data = $this->model->all();

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $modulename = $request->modulename;
            $getemployee = $this->employee->find($request->employee_id);
            $getuser = $this->user->where('username',$getemployee->LoginName)->get();
            $getExistUser = $this->model->where('employee_id',$request->employee_id)->where('req_id',$request->req_id)->where('module_id',$this->getModuleId($request->modulename))->get();
            
            if(count($getExistUser) < 1) {
                if($request->role && $request->modulename ='Mom') {
                    $getChairman = $this->model->where('req_id',$request->req_id)->where('module_id',$this->getModuleId($request->modulename))->where('role','Chairman')->get();
                    if(count($getChairman) > 0) {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['chairmanexist']]);
                    } else {
                        if($request->role == 'Chairman') {
                            $this->createApprChairman($request->employee_id, $request->modulename, $request->req_id);
                        }
                    }
                }

                if(count($getuser) > 0) {
                    $this->model->create($requestData);
                } else {
                    $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                    if ($getldap) {
                        $this->user->create([
                            "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                            "domain" => "default",
                            "username" => $getldap['samaccountname'][0],
                            "fullname" => $getldap['name'][0],
                            "email" => $getldap['mail'][0]
                        ]);
                        $this->model->create($requestData);
                    } else {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                    }

                }
            } else {
                return response()->json(["status" => "error", "message" => $this->getMessage()['userexist']]);
            }

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function getList($id,$modulename)
    {
        try {

                $data = $this->model->where('req_id',$id)->get();

                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();

            $data = $this->model->findOrFail($id);

            if($request->employee_id) {
                $getExistUser = $this->model->where('employee_id',$request->employee_id)->get();
                if(count($getExistUser) < 1) {

                    $getEmployee =  $this->employee->find($request->employee_id);
                    $getUser = $this->user->where('username',$getEmployee->LoginName)->get();

                    if(count($getUser) > 0) {
                        $data->update($requestData);
                    } else {
                        $getldap = LdapUser::findBy('samaccountname',$getEmployee->LoginName);

                        if ($getldap) {
                            $this->user->create([
                                "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                                "domain" => "default",
                                "username" => $getldap['samaccountname'][0],
                                "fullname" => $getldap['name'][0],
                                "email" => $getldap['mail'][0]
                            ]);
                            $data->update($requestData);
                        } else {
                            return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                        }
                    }

                    $this->createApprChairman($request->employee_id, $this->getModuleName($data->module_id), $data->req_id);

                } else {
                    // $data->update($requestData);
                    // $this->createApprChairman($request->employee_id, $this->getModuleName($data->module_id), $data->req_id);
                    return response()->json(["status" => "error", "message" => $this->getMessage()['userexist']]);
                }
            } else {
                if($request->role == 'Chairman' && $this->getModuleName($data->module_id) == 'Mom') {
                    $getChairman = $this->model->where('req_id',$data->req_id)->where('module_id',$data->module_id)->where('role','Chairman')->get();
                    if(count($getChairman) > 0) {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['chairmanexist']]);
                    } else {
                        $this->createApprChairman($data->employee_id, $this->getModuleName($data->module_id), $data->req_id);
                        $data->update($requestData);
                    }
                } else {
                    $this->deleteApprChairman($this->getModuleName($data->module_id), $data->req_id);
                    $data->update($requestData);
                }
            }

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);
            if($data->role == 'Chairman' && $this->getModuleName($data->module_id) == 'Mom') {
                $this->deleteApprChairman($this->getModuleName($data->module_id), $data->req_id);
            }
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
