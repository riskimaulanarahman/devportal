<?php

use App\Models\SideMenu;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

Route::middleware(['session.check'])->group(function () {

    if(Schema::hasTable('side_menus')) {
        $sidemenu = SideMenu::select('route')->where('route','!=','api')->get();
            foreach ($sidemenu as $menu_item) {
                Route::get('{menu_item}', [App\Http\Controllers\GeneratemenuController::class, 'index'])->name('index');
            }
    }
    
});
