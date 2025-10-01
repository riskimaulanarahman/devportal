<?php

namespace App\Http\Traits;

use DB;
use Auth;

use Carbon\Carbon;
use App\Models\Module;
use App\Models\Employee;
use App\Models\Approvaltype;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\Submission\Mom;
use App\Models\Submission\MMF\Mmf;
use App\Models\Submission\MMF\Mmf28;
use App\Models\Submission\MMF\Mmf30;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\User;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

trait ApproverTrait {

    public function createApproverList($moduleName, $req_id) // tidak di gunakan
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            $getApprover = Approvaluser::where('module', $moduleName)->where('isActive',1)->get();

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

    public function createApproverListCategory($moduleName, $req_id, $cat_id) // tidak di gunakan
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            if($cat_id !== null) {
                $getApprover = Approvaluser::where('module', $moduleName)
                ->whereRaw("',' + category_id + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$cat_id])
                ->where('isActive',1)
                ->orderBy('sequence')
                ->get();

                // Hapus data yang bersangkutan di tabel ApproverListReq
                ApproverListReq::where('module_id', $module->id)
                    ->where('req_id', $req_id)
                    ->delete();

                foreach ($getApprover as $approver) {
                    $approverList = new ApproverListReq();
                    $approverList->req_id = $req_id;
                    $approverList->approvalAction = 1;
                    $approverList->module_id = $module->id;
                    $approverList->approver_id = $approver->id;
                    $approverList->save();
                }
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

    public function createApprover($moduleName, $req_id, $company, $cat_id)
    {
        // dd($company);
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();

        // Periksa apakah modul ada dan bukan 'Mom'
        if (!$module || $module->module === 'Mom') {
            return; // Keluar jika modul tidak ditemukan atau modul adalah 'Mom'
        }

        $checkApprNull = 1;

        $getApprover = Approvaluser::select('tbl_approver.*')
            ->where('tbl_approver.module', $moduleName)
            ->where('tbl_approver.isActive', 1)
            ->where('tbl_approver.autoAdd', 1);

        if ($company !== null && $cat_id === null) {
            $getApprover->whereRaw("',' + companyList + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$company]);
        } elseif ($company === null && $cat_id !== null) {
            $getApprover->whereRaw("',' + category_id + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$cat_id]);
        } 
        elseif ($company !== null && $cat_id !== null) {
            $getApprover->whereRaw("',' + companyList + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$company])
                        ->whereRaw("',' + category_id + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$cat_id]);
        } else {
            $checkApprNull = 0;
        }

        $results = $getApprover->get();

        // Hapus data yang bersangkutan di tabel ApproverListReq
        $apprList = ApproverListReq::select('tbl_approverListReq.*')
                                    ->where('tbl_approverListReq.module_id', $module->id)
                                    ->where('tbl_approverListReq.req_id', $req_id);

        $exceptdel = [
            'Jdi',
            'ActiveDirectory',
            'Mmf',
            'MaterialReq',
            'Legal',
            'Memorandum',
            'Wphc',
        ];

        if (in_array($moduleName, $exceptdel)) {
            $apprList->leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
                    ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
                    ->whereNotIn('tbl_approvaltype.ApprovalType',[
                        'Department Head',
                        'Superior/Department Head',
                        'Superior',
                        'BCID Manager','Finance' // for JDI savingCost
                    ]);
        }

        $getApprList = $apprList->get();

        $apprList->delete();

        foreach ($results as $approver) {
            $approverList = new ApproverListReq();
            $approverList->req_id = $req_id;
            $approverList->module_id = $module->id;
            $approverList->approver_id = $approver->id;
            $approverList->save();
        }

        $appUserNull = Approvaluser::select('tbl_approver.*')
        ->where('tbl_approver.module', $moduleName)
        ->where('tbl_approver.isActive', 1)
        ->where('tbl_approver.autoAdd', 1)
        ->whereNull('companyList')
        ->whereNull('category_id');

        $except = [
            'Jdi',
            'ActiveDirectory',
            'Mmf',
            'MaterialReq',
        ];
        
        if (!in_array($moduleName, $except)) {
            $appUserNull->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
                        ->whereNotIn('tbl_approvaltype.ApprovalType',['Department Head','Buyer']);
        }

        $results2 = $appUserNull->get();

        // dd($results2);

        if($checkApprNull == 1) {
            foreach ($results2 as $approver2) {
                $approverList2 = new ApproverListReq();
                $approverList2->req_id = $req_id;
                $approverList2->module_id = $module->id;
                $approverList2->approver_id = $approver2->id;
                $approverList2->save();
            }
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

    public function approverAction($moduleName, $req_id, $type, $appraction, $remarks, $apprid)
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            if($type == 'Approver') {

                $ApproverListReq = ApproverListReq::select('tbl_approverListReq.*','tbl_approver.sequence')
                ->join('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                ->where('tbl_approverListReq.module_id', $module->id)
                ->where('tbl_approverListReq.req_id', $req_id)
                ->where('tbl_approverListReq.approver_id', $apprid)
                ->first();

                if ($ApproverListReq) {
                    $ApproverListReq->approvalDate = Carbon::now();
                    $ApproverListReq->save();
                }
            }

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

    // MOM ===============================================================
    public function createApprChairman($employeeID, $moduleName, $reqID) {
        if($moduleName == 'Mom') {

            $getemployee = Employee::find($employeeID);
            $getuser = $this->user->where('username',$getemployee->LoginName)->get();

            //START approver for Chairman
            $getIDapprTypeChairman = Approvaltype::where('Module','Mom')->where('ApprovalType','Chairman')->first();
            $checkExistApprChairman = Approvaluser::where('module','Mom')
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprTypeChairman->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = $this->user->create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistApprChairman) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 1;
                $approver->approvaltype_id = $getIDapprTypeChairman->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistApprChairman as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::where('module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->save();

            $this->updateMomColumnChairmanUserID($userID, $reqID);
            //END create approver for Chairman
        }
    }

    public function deleteApprChairman($moduleName, $reqID) {

        // Hapus data yang bersangkutan di tabel ApproverListReq
        ApproverListReq::where('module_id', $this->getModuleId($moduleName))
        ->where('req_id', $reqID)
        ->delete();

    }

    private function updateMomColumnChairmanUserID($userID, $reqID) {
        $MomReq = Mom::find($reqID);
        $MomReq->update([
            'chairman_userid' => $userID
        ]);
    }
    // MOM ===============================================================

    // JDI ===============================================================
    public function createApprManager($employeeID, $moduleName, $reqID) {
        // if($moduleName == 'Jdi') {
            $getemployee = Employee::find($employeeID);
            $getuser = $this->user->where('username',$getemployee->LoginName)->whereNotNull('guid')->get();
            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Department Head')->first();

            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = $this->user->create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 2;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Department Head')
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            // $approverList->approvalAction = ($reqStatus !== 0) ? 1 : 0;
            $approverList->save();

        // }
    }

    public function createApprAdditionalApprover($employeeID, $moduleName, $reqID) {
        // if($moduleName == 'Jdi') {

            $getemployee = Employee::find($employeeID);
            $getuser = User::where('username',$getemployee->LoginName)->whereNotNull('guid')->get();
            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Additional Approver')->first();

            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = User::create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;

                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 3;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Additional Approver')
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            $approverList->approvalAction = 1;
            $approverList->save();

        // }
    }

    public function createApprSaving($saving, $moduleName, $reqID, $reqStatus, $company) {

        if($moduleName == 'Jdi') {
            // dd($company);
            // Mengambil data ApprovalType terlebih dahulu untuk mengurangi duplikasi kode
            $getIDapprType = Approvaltype::where('Module','Jdi')->whereIn('ApprovalType',['Finance','BCID Manager'])->get();

            foreach ($getIDapprType as $apprType) {
                // Mengambil data Approvaluser berdasarkan approvaltype_id dan kondisi isActive
                $apprUsers = Approvaluser::where('module', 'Jdi')
                                        // ->where('employee_id', $employeeID) // Baris ini di-comment, bisa di-uncomment jika diperlukan
                                        ->where('approvaltype_id', $apprType->id)
                                        ->whereRaw("',' + companyList + ',' LIKE '%,' + CAST(? AS NVARCHAR) + ',%'", [$company])
                                        ->where('isActive', 1)
                                        ->get();

                foreach ($apprUsers as $appr) {
                    if ($saving == 1) {
                        // Jika $saving == 1, tambahkan approver baru
                        $approverList = new ApproverListReq();
                        $approverList->req_id = $reqID;
                        $approverList->module_id = $this->getModuleId($moduleName);
                        $approverList->approver_id = $appr->id;
                        $approverList->approvalAction = ($reqStatus !== 0) ? 1 : 0;
                        $approverList->save();
                    } else if ($saving == 0) {
                        // Jika $saving != 1, hapus approver yang ada
                        ApproverListReq::where('module_id', $this->getModuleId($moduleName))
                                    ->where('req_id', $reqID)
                                    ->where('approver_id', $appr->id)
                                    ->delete();
                    }
                }
            }

        }
    }

    // MMF ===============================================================
    public function createApprBuyer($employeeID, $reqID) {
        // if($moduleName == 'Jdi') {
            $moduleName = 'Mmf';
            $getemployee = Employee::find($employeeID);
            $getuser = $this->user->where('username',$getemployee->LoginName)->get();

            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Buyer')->first();
            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = $this->user->create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 4;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Buyer')
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            $approverList->approvalAction = 1;
            $approverList->save();

            $checkmmf = Mmf::find($reqID);

            if($checkmmf->category == 'MMF30') {
                $updbuyer30 = Mmf30::where('req_id',$reqID)->first();
                $updbuyer30->update([
                    'Buyer' => $employeeID
                ]);
            } else if($checkmmf->category == 'MMF28') {
                $updbuyer28 = Mmf28::where('req_id',$reqID)->first();
                $updbuyer28->update([
                    'Buyer' => $employeeID
                ]);
            }

        // }
    }

    public function deleteApprBuyer($employeeID, $reqID) {
        $moduleName = 'Mmf';
        // Hapus data yang bersangkutan di tabel ApproverListReq
        // $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Buyer')->first();
        // $checkExistAppr = Approvaluser::where('module',$moduleName)
        //                                 ->where('employee_id',$employeeID)
        //                                 ->where('approvaltype_id',$getIDapprType->id)
        //                                 ->where('isActive',1)
        //                                 ->get();
        // ApproverListReq::where('module_id', $this->getModuleId($moduleName))
        // ->where('approvaltype_id',$getIDapprType->id)
        // ->where('req_id', $reqID)
        // ->delete();
        ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approver.employee_id', $employeeID)
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Buyer')
            ->delete();

        $checkmmf = Mmf::find($reqID);

        if($checkmmf->category == 'MMF30') {
            $updbuyer30 = Mmf30::where('req_id',$reqID)->first();
            $updbuyer30->update([
                'Buyer' => null
            ]);
        } else if($checkmmf->category == 'MMF28') {
            $updbuyer28 = Mmf28::where('req_id',$reqID)->first();
            $updbuyer28->update([
                'Buyer' => null
            ]);
        }

    }

    //Memorandum
    public function createApprSuperiorDepthead($employeeID, $moduleName, $reqID) {
        // if($moduleName == 'Memorandum') {
            $getemployee = Employee::find($employeeID);
            $getuser = $this->user->where('username',$getemployee->LoginName)->whereNotNull('guid')->get();
            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Superior/Department Head')->first();
            
            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = $this->user->create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 2;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Superior/Department Head')
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            // $approverList->approvalAction = ($reqStatus !== 0) ? 1 : 0;
            $approverList->save();

        // }
    }
    
    //WPHC/////////////////////////////////////////////////////////////////////////
    public function createApprSuperior($employeeID, $moduleName, $reqID) {
        // if($moduleName == 'Legal') {
            $getemployee = Employee::find($employeeID);
            $getuser = $this->user->where('username',$getemployee->LoginName)->whereNotNull('guid')->get();
            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Superior')->first();

            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = $this->user->create([
                        "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;
                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 2;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            // ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            // ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            // ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            // ->where('req_id', $reqID)
            // ->where('tbl_approvaltype.ApprovalType','Superior')
            // ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            // $approverList->approvalAction = ($reqStatus !== 0) ? 1 : 0;
            $approverList->save();

        // }
    }

    public function createApprDeptHead($employeeID, $moduleName, $reqID) {

            $getemployee = Employee::find($employeeID);
            $getuser = User::where('username',$getemployee->LoginName)->whereNotNull('guid')->get();
            //START approver for Chairman
            $getIDapprType = Approvaltype::where('Module',$moduleName)->where('ApprovalType','Department Head')->first();

            $checkExistAppr = Approvaluser::where('module',$moduleName)
                                        ->where('employee_id',$employeeID)
                                        ->where('approvaltype_id',$getIDapprType->id)
                                        ->where('isActive',1)
                                        ->get();

            if(count($getuser) > 0) {
                $userID = $this->getUser($getemployee->LoginName)->id;
            } else {
                
                $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                if ($getldap) {
                    $newUser = User::create([
                        "guid" => $getldap->getConvertedGuid(),
                        "domain" => "default",
                        "username" => $getldap['samaccountname'][0],
                        "fullname" => $getldap['name'][0],
                        "email" => $getldap['mail'][0]
                    ]);

                    $userID = $newUser->id;

                } else {
                    return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                }

            }

            if(count($checkExistAppr) < 1) {
                $approver = new Approvaluser();
                $approver->module = $moduleName;
                $approver->user_id = $userID;
                $approver->employee_id = $employeeID;
                $approver->sequence = 3;
                $approver->approvaltype_id = $getIDapprType->id;
                $approver->save();

                // Mengambil ID dari $approver yang baru disimpan
                $newApproverId = $approver->id;
            } else {
                foreach($checkExistAppr as $item) {
                    $newApproverId = $item->id;
                }
            }

            // Hapus data yang bersangkutan di tabel ApproverListReq
            ApproverListReq::leftJoin('tbl_approver','tbl_approverListReq.approver_id','tbl_approver.id')
            ->leftJoin('tbl_approvaltype','tbl_approver.approvaltype_id','tbl_approvaltype.id')
            ->where('tbl_approverListReq.module_id', $this->getModuleId($moduleName))
            ->where('req_id', $reqID)
            ->where('tbl_approvaltype.ApprovalType','Department Head')
            ->delete();

            $approverList = new ApproverListReq();
            $approverList->req_id = $reqID;
            $approverList->module_id = $this->getModuleId($moduleName);
            $approverList->approver_id = $newApproverId;
            $approverList->approvalDate = null;
            $approverList->save();
    }
}