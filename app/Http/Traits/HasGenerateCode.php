<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Auth;
use DB;

trait HasGenerateCode {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    public static function generateCode($moduleName)
    {   
        // Check if the module name exists
        $module = DB::table('tbl_module')->where('module', $moduleName)->first();
        if (!$module) {
            // Create a new module
            $module = DB::table('tbl_module')->insertGetId([
                'module' => $moduleName,
            ]);
        }

        // Get the current year and month
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->format('m');

        // Get the highest code for this module and year/month
        $highestCodeQuery = DB::table('codes')
        ->select('code')
        ->where('code', 'like', "{$module->module}/{$year}/{$month}/%")
        ->orderByDesc('created_at')
        ->limit(1);

        $highestCode = $highestCodeQuery->value('code');

        // Increment the code value or start with 0001 if no existing codes
        if (!$highestCode) {
            // No existing code found, start at 1
            $newCode = 1;
        } else {
            // Extract the last 4 characters of the code and convert to integer
            $highestCode = intval(substr($highestCode, -4));
            // Increment the highest code to get the new code
            $newCode = $highestCode + 1;
        }

        $codeFormat = str_pad($newCode, 4, '0', STR_PAD_LEFT);

        // Generate the final code
        $finalCode = $moduleName . '/' . $year . '/' . $month . '/' . $codeFormat;

        // Create a new code in the table_code table
        $codeId = DB::table('codes')->insertGetId([
            'module_id' => $module->id,
            'code' => $finalCode,
            'created_at' => $now,
        ]);


        return $codeId;
    }

}