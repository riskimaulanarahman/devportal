<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Employee;
use App\Models\Theme;
use App\Models\Developer;

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
                $developer = Developer::where('user_id',$user->id)->first();
                // dd($employee);
                View::share('themes', $themes);
                View::share('employee', $employee);
                View::share('developer', $developer);
            }

        });
        Schema::defaultStringLength(191);

    }
}
