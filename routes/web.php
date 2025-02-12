<?php

use App\Models\SideMenu;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::get('/check-session',[App\Http\Controllers\SessionCheckController::class, 'checkSession']);

// dashboard
Route::get('dashboardproject',[App\Http\Controllers\Submission\ProjectRequestController::class, 'dashboard'])->name('dashboardproject'); //Dashboard project
Route::get('cvaf',[App\Http\Controllers\Submission\CvafRequestController::class, 'showEvaluationForm'])->name('cvaf'); //cvaf
Route::post('calculate-score', [App\Http\Controllers\Submission\CvafRequestController::class, 'calculateScore']);

Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

Route::middleware(['session.check'])->group(function () {

    if(Schema::hasTable('reference.side_menus')) {
        $sidemenu = SideMenu::select('route')->where('route','!=','api')->get();
            foreach ($sidemenu as $menu_item) {
                Route::get('{menu_item}', [App\Http\Controllers\GeneratemenuController::class, 'index'])->name('index');
            }
    }

//dashboard

// Scheduler
Route::get('/mom-reminder/{mode}',[App\Http\Controllers\Submission\MomTaskUpdateController::class, 'reminderNotificationMessage'])->name('mom-reminder');

// Generate PDF
Route::get('/gen-pdf/jdi/{id}',[App\Http\Controllers\Submission\JdiRequestController::class, 'genPdfJdi'])->name('gen-pdf-jdi');
Route::get('/gen-pdf/activedirectory/{id}',[App\Http\Controllers\Submission\IT\ADRequestController::class, 'genPdfAD'])->name('gen-pdf-ad');
Route::get('/gen-pdf/mom/{id}', [App\Http\Controllers\Submission\MomTaskUpdateController::class, 'genPdfMom'])->name('gen-pdf-mom');
Route::get('/gen-pdf/material/{id}', [App\Http\Controllers\Submission\Ecatalog\MaterialRequestController::class, 'genPdfMaterialReq'])->name('gen-pdf-material');
Route::get('/gen-pdf/mmf28/{id}', [App\Http\Controllers\Submission\MMF\M28RequestController::class, 'genPdfMmfReq'])->name('gen-pdf-28');
Route::get('/gen-pdf/mmf30/{id}', [App\Http\Controllers\Submission\MMF\M30RequestController::class, 'genPdfMmfReq'])->name('gen-pdf-m30');
Route::get('/gen-pdf/hcrf/{id}', [App\Http\Controllers\Submission\HRIS\Hcrf\HcrfRequestController::class, 'genPdfHcrfReq'])->name('gen-pdf-hcrf');

});