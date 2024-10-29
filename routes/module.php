<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::group(['prefix' => 'api'], function () {
    Route::apiResources([
        'headcounts' => App\Http\Controllers\Module\HeadcountsController::class,
        'historyhcbu' => App\Http\Controllers\Module\HistoryhcbuController::class,
        'historyhcdepartment' => App\Http\Controllers\Module\HistoryhcdepartmentController::class,
        'historyhclocation' => App\Http\Controllers\Module\HistoryhclocationController::class,
        'historyhcposition' => App\Http\Controllers\Module\HistoryhcpositionController::class,
        'historyhcgrade' => App\Http\Controllers\Module\HistoryhcgradeController::class,
        'historyhclevel' => App\Http\Controllers\Module\HistoryhclevelController::class,
        'historyhcappraisal' => App\Http\Controllers\Module\HistoryhcappraisalController::class,
        'employeedata' => App\Http\Controllers\Module\EmployeedataController::class,
        'ecatalog' => App\Http\Controllers\Module\EcatalogController::class,
        'purchasinguser' => App\Http\Controllers\Module\PurchasinguserController::class,
    ]);
});