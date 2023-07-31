<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::group(['prefix' => 'api'], function () {
    Route::apiResources([
        'user' => App\Http\Controllers\Admin\UserController::class,
        'module' => App\Http\Controllers\Admin\ModuleController::class,
        'useraccess' => App\Http\Controllers\Admin\UseraccessController::class,
        'sidemenu' => App\Http\Controllers\Admin\SidemenuController::class,
        'icons' => App\Http\Controllers\Admin\IconsController::class,
        'sequence' => App\Http\Controllers\Admin\SequenceController::class,
        'theme' => App\Http\Controllers\Admin\ThemeController::class,
        'company' => App\Http\Controllers\Admin\CompanyController::class,
        'department' => App\Http\Controllers\Admin\DepartmentController::class,
        'grade' => App\Http\Controllers\Admin\GradeController::class,
        'level' => App\Http\Controllers\Admin\LevelController::class,
        'location' => App\Http\Controllers\Admin\LocationController::class,
        'position' => App\Http\Controllers\Admin\PositionController::class,
        'ethnic' => App\Http\Controllers\Admin\EthnicController::class,
        'religion' => App\Http\Controllers\Admin\ReligionController::class,
        'nationality' => App\Http\Controllers\Admin\NationalityController::class,
        'approvaltype' => App\Http\Controllers\Admin\ApprovaltypeController::class,
        'approvaluser' => App\Http\Controllers\Admin\ApprovaluserController::class,
        'developer' => App\Http\Controllers\Admin\DeveloperController::class,
    ]);
    Route::post('/update-profilepicture', [App\Http\Controllers\HomeController::class, 'updateProfilePicture']);
});