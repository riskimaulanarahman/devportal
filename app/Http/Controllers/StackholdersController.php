<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Stackholders;
use App\Models\Employee;
use App\Models\User;

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


            //     $user = LdapUser::findBy('samaccountname','riski_maulana');
            //     if ($user) {
            //         // The user is an immediate member of the 'Accounting' group.
            //         echo 'oke';
            //     } else {
            //         echo 'nok';
            //     }
          
            // return false;


            // return $user->getDiagnosticMessage();
           
            

            // $username = 'riski_maulana';
            // $users = User::where('username', '=', $username)->get();
            // foreach ($users as $user) {
            // }

            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $getemployee = $this->employee->find($request->employee_id);
            $getuser = $this->user->where('username',$getemployee->LoginName)->get();
            if(count($getuser) > 0) {
                $this->model->create($requestData);
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $this->user->create([
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);
                    $this->model->create($requestData);
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

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
            // $module = $this->module->select('id','module')->where('module',$modulename)->first();
            // if($module) {
                $data = $this->model->where('req_id',$id)
                // ->where('module_id',$module->id)
                ->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            // } else {
            //     return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            // }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();

            $data = $this->model->findOrFail($id);
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
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
