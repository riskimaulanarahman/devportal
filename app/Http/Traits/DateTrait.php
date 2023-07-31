<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;

trait DateTrait {

    public function addOneDayToDate(&$requestData)
    {
        foreach ($requestData as $key => $value) {
            if (is_string($value) && strlen($value) > 1 && strtotime($value) !== false) {
                $date = Carbon::parse($value);
                $date->addDay();
                $requestData[$key] = $date->format('Y-m-d');
            }
        }
    }

}