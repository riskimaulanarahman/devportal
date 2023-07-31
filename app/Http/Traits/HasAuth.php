<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Auth;

use App\Models\Employee;

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

}