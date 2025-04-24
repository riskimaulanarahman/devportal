<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::group(['prefix' => 'api'], function () {
    Route::apiResources([
        'projectrequest' => App\Http\Controllers\Submission\ProjectRequestController::class,
        'projectreport' => App\Http\Controllers\Submission\ProjectReportController::class,
        'ticketrequest' => App\Http\Controllers\Submission\TicketRequestController::class,
        'missionrequest' => App\Http\Controllers\Submission\UavMissionRequestController::class,
        'missionrequestdetail' => App\Http\Controllers\Submission\UavMissionRequestDetailController::class,
        'momrequest' => App\Http\Controllers\Submission\MomRequestController::class,
        'momtaskdetail' => App\Http\Controllers\Submission\MomTaskDetailController::class,
        'momtaskbound' => App\Http\Controllers\Submission\MomTaskBoundController::class,
        'momtaskupdate' => App\Http\Controllers\Submission\MomTaskUpdateController::class,
        //HRSC
        'hrscrequest' => App\Http\Controllers\Submission\HrscRequestController::class,
        'hrscreport' => App\Http\Controllers\Submission\HrscReportController::class,
        //ghm
        'ghmrequest' => App\Http\Controllers\Submission\GhmRequestController::class,
        'ghm_booking' => App\Http\Controllers\Submission\GhmRequestController::class,
        // 'ghm_approve' => App\Http\Controllers\AutoapproveghmController::class,
        // BCID - JDI
        'jdirequest' => App\Http\Controllers\Submission\JdiRequestController::class,
        'jdireport' => App\Http\Controllers\Submission\JdiReportController::class,
        // IT - Active Directory
        'adrequest' => App\Http\Controllers\Submission\IT\ADRequestController::class,
        // procurement - mmf
        'mmf28request' => App\Http\Controllers\Submission\MMF\M28RequestController::class,
        'mmf30request' => App\Http\Controllers\Submission\MMF\M30RequestController::class,
        'mmf30detail' => App\Http\Controllers\Submission\MMF\M30RequestDetailController::class,
        // procurement - material
        'materialrequest' => App\Http\Controllers\Submission\Ecatalog\MaterialRequestController::class,
        'materialdetail' => App\Http\Controllers\Submission\Ecatalog\MaterialRequestDetailController::class,
        // financial - advance
        'advancerequest' => App\Http\Controllers\Submission\Financial\Advance\AdvanceRequestController::class,
        'advancedetail' => App\Http\Controllers\Submission\Financial\Advance\AdvanceRequestDetailController::class,
        // HRIS - hcrf
        // 'hcrfrequest' => App\Http\Controllers\Submission\HRIS\Hcrf\HcrfRequestController::class,
        // 'hcrfdetail' => App\Http\Controllers\Submission\HRIS\Hcrf\HcrfRequestDetailController::class,
        // financial - CCM
        'ccmrequest' => App\Http\Controllers\Submission\Financial\Ccm\CcmRequestController::class,
        'ccmdetail' => App\Http\Controllers\Submission\Financial\Ccm\CcmRequestDetailController::class,
        // financial - Capex
        'capexrequest' => App\Http\Controllers\Submission\Financial\Capex\CapexRequestController::class,
        'capexdetail' => App\Http\Controllers\Submission\Financial\Capex\CapexRequestDetailController::class,
        'capexjustification' => App\Http\Controllers\Submission\Financial\Capex\CapexRequestJustificationController::class,
        // 'capexquestion' => App\Http\Controllers\Submission\Financial\Capex\CapexRequestJustificationController::class,
        
        //memor
        'memorandum_request' => App\Http\Controllers\Submission\MemorandumController::class,

        'attachmentrequest' => App\Http\Controllers\AttachmentController::class,
        'approverlistrequest' => App\Http\Controllers\ApproverListController::class,
        'assignmentto' => App\Http\Controllers\AssignmenttoController::class,
        'stackholders' => App\Http\Controllers\StackholdersController::class,
        'categorysubmission' => App\Http\Controllers\CategoryController::class,
    ]);

    //get detail request
    Route::get('attachmentrequest/{id}/{modulename}',[App\Http\Controllers\AttachmentController::class, 'getList']); //get list attachment by req_id of module
    Route::get('approverlistrequest/{id}/{modulename}',[App\Http\Controllers\ApproverListController::class, 'getList']); //get list approver by req_id of module
    Route::get('approverlisthistory/{id}/{modulename}',[App\Http\Controllers\ApproverHistoryController::class, 'getList']); //get list approver history by req_id of module
    Route::get('assignmentto/{id}/{modulename}',[App\Http\Controllers\AssignmenttoController::class, 'getList']); //get list developer by req_id of module
    Route::get('stackholders/{id}/{modulename}',[App\Http\Controllers\StackholdersController::class, 'getList']); //get list stackholders by req_id of module
    Route::get('memorandumhis/{id}/{modulename}',[App\Http\Controllers\ContractHistoryController::class, 'getList']); //get list contract history by req_id of module
    Route::post('memorandumhis',[App\Http\Controllers\ContractHistoryController::class, 'store']); //get list contract history by req_id of module
    Route::put('memorandumhis/{id}',[App\Http\Controllers\ContractHistoryController::class, 'update']); //get list contract history by req_id of module
    Route::delete('memorandumhis/{id}',[App\Http\Controllers\ContractHistoryController::class, 'destroy']); //get list contract history by req_id of module
    // uav mission
    Route::get('missionrequestdetail/{id}/{modulename}',[App\Http\Controllers\Submission\UavMissionRequestDetailController::class, 'getList']); //get list missionrequestdetail by req_id of module
    // category
    Route::get('categorysubmission/{id}/{modulename}',[App\Http\Controllers\CategoryController::class, 'getList']); //get list categorysubmission by req_id of module
    // MoM
    Route::get('momtaskdetail/{id}/{modulename}',[App\Http\Controllers\Submission\MomTaskDetailController::class, 'getList']); //get list momtaskdetail by req_id of module
    Route::get('momtaskbound/{id}/{modulename}',[App\Http\Controllers\Submission\MomTaskBoundController::class, 'getList']); //get list momtaskbound by req_id of module
    Route::get('momtaskupdate/{id}/{modulename}',[App\Http\Controllers\Submission\MomTaskUpdateController::class, 'getList']); //get list momtaskbound by req_id of module
    // procurement - MMF 30
    Route::get('mmf30detail/{id}/{modulename}',[App\Http\Controllers\Submission\MMF\M30RequestDetailController::class, 'getList']); //get list mmf30 by req_id of module
    Route::get('mmf30historyApp',[App\Http\Controllers\Submission\MMF\M30RequestController::class, 'historyApprover']); //get list mmf30historyApp of module
    Route::get('mmf30report',[App\Http\Controllers\Submission\MMF\M30RequestController::class, 'report']); //get list report of module
    // procurement - material (eCatalog)
    Route::get('materialdetail/{id}/{modulename}',[App\Http\Controllers\Submission\Ecatalog\MaterialRequestDetailController::class, 'getList']); //get list material req by req_id of module
    
    // financial - advance
    Route::get('advancedetail/{id}/{modulename}',[App\Http\Controllers\Submission\Financial\Advance\AdvanceRequestDetailController::class, 'getList']); //get list advance by req_id of module
    Route::get('advancehistoryApp',[App\Http\Controllers\Submission\Financial\Advance\AdvanceRequestController::class, 'historyApprover']); //get list advancehistoryApp of module
    Route::get('advancereport',[App\Http\Controllers\Submission\Financial\Advance\AdvanceRequestController::class, 'report']); //get list report of module
    //  HRIS - hcrf
    // Route::get('hcrfdetail/{id}/{modulename}',[App\Http\Controllers\Submission\HRIS\Hcrf\HrisRequestDetailController::class, 'getList']); //get list hcrf by req_id of module
    // Route::get('hcrfhistoryApp',[App\Http\Controllers\Submission\HRIS\Hcrf\HrisRequestController::class, 'historyApprover']); //get list hcrfhistoryApp of module
    // Route::get('hcrfreport',[App\Http\Controllers\Submission\HRIS\Hcrf\HrisRequestController::class, 'report']); //get list report of module
    //  financial - ccm
    Route::get('ccmdetail/{id}/{modulename}',[App\Http\Controllers\Submission\Financial\Ccm\CcmRequestDetailController::class, 'getList']); //get list ccm by req_id of module
    Route::post('ccmcheckbalance/{id}',[App\Http\Controllers\Submission\Financial\Ccm\CcmRequestController::class, 'checkBalance']); // check balance ccm with detail

    // financial - capex
    Route::get('capexdetail/{id}/{modulename}',[App\Http\Controllers\Submission\Financial\Capex\CapexRequestDetailController::class, 'getList']); //get list capex by req_id of module
    Route::get('capexjustification/{id}/{modulename}',[App\Http\Controllers\Submission\Financial\Capex\CapexRequestJustificationController::class, 'getList']); //get list capex by req_id of module
    
    //Ghm
    Route::post('checkattachmentghm',[App\Http\Controllers\Submission\GhmRequestController::class, 'checkattachmentghm']); //get ghm checkattachmentghm
    
    //action
    Route::post('submissionrequest/{id}/{modulename}',[App\Http\Controllers\Submission\SubmissionController::class, 'submit']); //submit submission request
    Route::post('submissioncheckfields/{id}/{modulename}',[App\Http\Controllers\Submission\SubmissionController::class, 'checkFields']); //submit submission request
    Route::post('taskapproval/{id}/{modulename}',[App\Http\Controllers\Submission\MomTaskUpdateController::class, 'taskactions']); //submit submission request

    //list
    Route::get('list-getemployee',[App\Http\Controllers\ListController::class, 'listEmployee']); //get list employee
    Route::get('list-employeesamedept',[App\Http\Controllers\ListController::class, 'listEmployeeSameDept']); //get list employee same department

});
