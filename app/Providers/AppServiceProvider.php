<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Employee;
use App\Models\Theme;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            
            if (Auth::check()) {
                $user = Auth::user();
                $employee = Employee::where('LoginName',Auth::user()->username)->first();
                $themes = Theme::where('user_id',$user->id)->get();
                // dd($employee);
                View::share('themes', $themes);
                View::share('employee', $employee);
            }

        });
        Schema::defaultStringLength(191);

    }
}
