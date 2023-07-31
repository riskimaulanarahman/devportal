<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use Auth;

trait ApproverTrait {

    public function createApproverList($moduleName, $req_id)
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            $getApprover = Approvaluser::where('module', $moduleName)->get();

            foreach ($getApprover as $approver) {
                $approverList = new ApproverListReq();
                $approverList->req_id = $req_id;
                $approverList->module_id = $module->id;
                $approverList->approver_id = $approver->id;
                $approverList->save();
            }

            // add creator
            $history = new ApproverListHistory();
            $history->req_id = $req_id;
            $history->module_id = $module->id;
            $history->fullName = Auth::user()->fullname;
            $history->approvalType = 'Originator';
            $history->approvalAction = 0;
            $history->approvalDate = Carbon::now();
            $history->save();

        }
    }

    public function approverAction($moduleName, $req_id, $type, $appraction, $remarks)
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            $history = new ApproverListHistory();
            $history->req_id = $req_id;
            $history->module_id = $module->id;
            $history->fullName = Auth::user()->fullname;
            $history->approvalType = $type;
            $history->approvalDate = Carbon::now();
            $history->approvalAction = $appraction;
            $history->remarks = $remarks;
            $history->save();
        }
    }

}