<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Auth;

use App\Models\Employee;
use App\Models\Developer;
use App\Models\User;
use App\Models\Company;

trait HasAuth {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    public function getAuth() {

        $user = Auth::user();

        return $user;

    }

    public function getEmployeeID() {
        if($this->getAuth()) {
            $result = Employee::where('LoginName',$this->getAuth()->username)->first();
        } else {
            $result = Employee::where('LoginName','planning_admin')->first();
        }

        return $result;
    }

    public function getUser($loginName) {

        $data = User::where('username',$loginName)->first();

        return $data;
    }

    public function getUserByid($id) {

        $data = User::find($id);

        return $data;
    }

    public function isDeveloper() {
        if($this->getAuth()) {
            $data = Developer::where('user_id',$this->getAuth()->id)->count();
        }
        if($data > 0) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    public function getCompanyName($id) 
    {
        $data = Company::select('id', 'CompanyCode')->where('id', $id)->first();
        if ($data) {
            return $data->CompanyCode;
        }
        return null;
    }

    public function getsysid($name) 
    {
        $data = Employee::select('id', 'sys_id', 'FullName')->where('FullName', $name)->first();
        if ($data) {
            return $data->sys_id;
        }
        return null;
    }

    public function getemployeename($sysid) 
    {
        $data = Employee::select('id', 'sys_id', 'FullName')->where('sys_id', $sysid)->first();
        if ($data) {
            return $data->FullName;
        }
        return null;
    }

    public function getDeptheadbyIDemployee($id) 
    {
        $data = Employee::select('id', 'sys_id', 'sys_id_depthead', 'FullName')->where('id', $id)->first();
        if ($data) {
            $depthead = Employee::select('id', 'sys_id', 'FullName')->where('sys_id', $data->sys_id_depthead)->first();
            return $depthead->id;
        }
        return null;
    }

    public function getEmployeeByID($id) 
    {
        $data = Employee::select('*')->where('id', $id)->first();
        if ($data) {
            return $data;
        }
        return null;
    }

}