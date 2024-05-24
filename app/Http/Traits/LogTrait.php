<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Auth;

use App\Models\Employee;
use App\Models\Developer;
use App\Models\User;
use App\Models\LogSuccess;
use App\Models\LogError;

trait LogTrait {

    // public $ip;
    // public $url;

    // public function __construct()
    // {
    //     $this->ip = $request->ip();
    //     $this->url = $request->url();
    // }

    function logsuccess($ip, $url, $action, $values) {
        $requestData = [
            "user" => $ip,
            "url" => $url,
            "action" => $action,
            "values" => $values
        ];
        LogSuccess::create($requestData);
    }
    
    function logerror($ip, $url, $action, $values) {
        $requestData = [
            "user" => $ip,
            "url" => $url,
            "action" => $action,
            "values" => $values
        ];
        LogError::create($requestData);
    }
    

}