<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::group(['prefix' => 'api'], function () {
    Route::apiResources([
        'travelrequest' => App\Http\Controllers\Submission\TravelRequestController::class,
        'projectrequest' => App\Http\Controllers\Submission\ProjectRequestController::class,
        'ticketrequest' => App\Http\Controllers\Submission\TicketRequestController::class,
        
        'attachmentrequest' => App\Http\Controllers\AttachmentController::class,
        'approverlistrequest' => App\Http\Controllers\ApproverListController::class,
        'assignmentto' => App\Http\Controllers\AssignmenttoController::class,
        'stackholders' => App\Http\Controllers\StackholdersController::class,
    ]);

    //get detail request
    Route::get('attachmentrequest/{id}/{modulename}',[App\Http\Controllers\AttachmentController::class, 'getList']); //get list attachment by req_id of module
    Route::get('approverlistrequest/{id}/{modulename}',[App\Http\Controllers\ApproverListController::class, 'getList']); //get list approver by req_id of module
    Route::get('approverlisthistory/{id}/{modulename}',[App\Http\Controllers\ApproverHistoryController::class, 'getList']); //get list approver history by req_id of module
    Route::get('assignmentto/{id}/{modulename}',[App\Http\Controllers\AssignmenttoController::class, 'getList']); //get list developer by req_id of module
    Route::get('stackholders/{id}/{modulename}',[App\Http\Controllers\StackholdersController::class, 'getList']); //get list stackholders by req_id of module

    //action
    Route::post('submissionrequest/{id}/{modulename}',[App\Http\Controllers\Submission\SubmissionController::class, 'submit']); //submit submission request

    //list
    Route::get('list-getemployee',[App\Http\Controllers\ListController::class, 'listEmployee']); //get list employee
    Route::get('list-employeesamedept',[App\Http\Controllers\ListController::class, 'listEmployeeSameDept']); //get list employee same department
});
