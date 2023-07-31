<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Http\Traits\HasAuth;
use App\Http\Traits\HasMessage;
use App\Http\Traits\HasGenerateCode;
use App\Http\Traits\DateTrait;
use App\Http\Traits\HasGetModule;
use App\Http\Traits\ApproverTrait;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    use HasAuth, HasMessage, HasGenerateCode, DateTrait, HasGetModule, ApproverTrait;

}
