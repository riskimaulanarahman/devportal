<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Approvaluser;
use App\Models\User;
use App\Models\Employee;
use DB;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class ApprovaluserController extends Controller
{

    private $model;

    public function __construct()
    {
        $this->model = new Approvaluser();
        $this->user = new User();
        $this->employee = new Employee();
    }

    public function index()
    {
        try {

            $data = $this->model->all();
            foreach ($data as &$item) {
                $categoryIds = explode(',', $item['category_id']);
            
                if (is_array($categoryIds)) {
                    $categoryNames = DB::table('tbl_categoryform')
                        ->whereIn('id', $categoryIds)
                        ->pluck('nameCategory')
                        ->toArray();
            
                    $categories = array_map(function ($id, $name) {
                        return $name ? $name : $id;
                    }, $categoryIds, $categoryNames);
            
                    $item['category_id'] = implode(' & ', $categories);
                }
            }

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            $getEmployee =  $this->employee->find($request->employee_id);
            $getUser = $this->user->where('username',$getEmployee->LoginName)->get();
            
            if($request->category_id) {
                $requestData['category_id'] = implode(',', $request->category_id);
            }

            if(count($getUser) > 0) {
                $requestData['user_id'] = $getUser[0]->id;
                $this->model->create($requestData);
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getEmployee->LoginName);

                if ($getldap) {
                    $createdUser = $this->user->create([
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);
                    $requestData['user_id'] = $createdUser->id;
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

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();
            if($request->category_id) {
                $requestData['category_id'] = implode(',', $request->category_id);
            }

            $data = $this->model->findOrFail($id);

            if($request->employee_id) {
                $getEmployee =  $this->employee->find($request->employee_id);
                $getUser = $this->user->where('username',$getEmployee->LoginName)->get();
                
                if(count($getUser) > 0) {
                    $requestData['user_id'] = $getUser[0]->id;
                    $data->update($requestData);
                } else {
                    $getldap = LdapUser::findBy('samaccountname',$getEmployee->LoginName);

                    if ($getldap) {
                        $createdUser = $this->user->create([
                            "username" => $getldap['samaccountname'][0],
                            "fullname" => $getldap['name'][0],
                            "email" => $getldap['mail'][0]
                        ]);
                        $requestData['user_id'] = $createdUser->id;
                        $data->update($requestData);
                    } else {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                    }

                }
            } else {
                $data->update($requestData);
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
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
