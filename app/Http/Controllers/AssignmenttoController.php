<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Assignmentto;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\ApproverListReq;
use App\Models\Approvaluser;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class AssignmenttoController extends Controller
{

    private $model;
    public $module;

    public function __construct()
    {
        $this->model = new Assignmentto();
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

            // MMF
            if($request->modulename == 'Mmf') {
                $checkRow = $this->model->where('module_id',$this->getModuleId($request->modulename))->where('req_id',$request->req_id)->count();
                if($checkRow < 1) {
                    $this->createApprBuyer($request->employee_id, $request->req_id);
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['buyerexist']]);
                }
            }

            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            if($request->employee_id) {
                $getemployee = $this->employee->find( $request->employee_id);
                $getuser = $this->user->where('username',$getemployee->LoginName)->get();
                
                if(count($getuser) > 0) {
                    $this->model->create($requestData);
                } else {
                    $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                    if ($getldap) {
                        $this->user->create([
                            // "guid" => Str::uuid(), // Add UUID here
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
            }else{
                $this->model->create($requestData);
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
            $module = $this->module->select('id','module')->where('module',$modulename)->first();
            if($module) {
                $data = $this->model->where('req_id',$id)
                ->where('module_id',$module->id)
                ->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            } else {
                return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            }

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
                $getEmployee =  $this->employee->find($request->employee_id);
                $getUser = $this->user->where('username',$getEmployee->LoginName)->get();


                if(count($getUser) > 0) {
                    $data->update($requestData);
                } else {
                    $getldap = LdapUser::findBy('samaccountname',$getEmployee->LoginName);

                    if ($getldap) {
                        $this->user->create([
                            // "guid" => Str::uuid(), // Add UUID here
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
            } else {
                $data->update($requestData);
            }

            // BUYER MMF
            if($this->getModuleName($data->module_id) == 'Mmf') {
                $this->createApprBuyer($data->employee_id, $data->req_id);
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
            
            // BUYER MMF
            if($this->getModuleName($data->module_id) == 'Mmf') {
                $this->deleteApprBuyer($data->employee_id, $data->req_id);
            }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
