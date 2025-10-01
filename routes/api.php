<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('upload-berkas/{modname}',[App\Http\Controllers\BerkasController::class, 'update'])->name('uploadberkas'); // upload attachment

//check
Route::post('getlogin',[App\Http\Controllers\MainController::class, 'getlogin']); // check login user exist
Route::post('/check-user-access',[App\Http\Controllers\Admin\UseraccessController::class, 'show']);
Route::post('/changedepthead',[App\Http\Controllers\Module\EmployeedataController::class, 'changeDeptHead']);



Route::apiResources([
    'logsuccess' => App\Http\Controllers\LogSuccessController::class,
    'logerror' => App\Http\Controllers\LogErrorController::class,
]);