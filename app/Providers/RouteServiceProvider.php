<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Icon;
use App\Models\Sequence;
use App\Models\SideMenu;
use App\Models\Useraccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

use Illuminate\Pagination\Paginator;
use View;
use Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';
    
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
            
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/module.php'));
            
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/submission.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/list.php'));
        });

        /**
         * Configure dynamic route for the sidebar
         * 
         * @return array
         */

         Paginator::useBootstrap();
         View::composer('*', function($view)
         {
            $icon = Icon::orderBy('name')->get();
        
            $view->with('icon', $icon);
         });
         View::composer('*', function($view)
         {
            $getuser = Auth::user();
            $item = SideMenu::where(function($query) use ($getuser){
                if(isset($getuser->isAdmin)) {
                    if($getuser->isAdmin == 1) {
                        $query->where('is_admin',1);
                        $query->orWhere('is_admin',0);
                    } else {
                        $query->where('is_admin',0);
                        $checkaccess = Useraccess::join('side_menus','tbl_useraccess.module_id','side_menus.modules')
                        ->where('employee_id',$getuser->id)
                        ->where('allowView',true)
                        ->get();
                        foreach ($checkaccess as $key) {
                            if($key->modules == $key->module_id) {
                                $query->orWhere('modules',$key->module_id);
                            }
                        }
                    }
                }
            })
            ->where('is_active', 1)
            ->orderBy('title')
            ->get();
            
            $view->with('sidemenu', $item);
         });

         View::composer('*', function($view)
         {
            $getuser = Auth::user();
            $sequence_menu = Sequence::where(function($query) use ($getuser){
                if(isset($getuser->isAdmin)) {

                    if($getuser->isAdmin == 1) {
                        $query->where('is_admin',1);
                        $query->orWhere('is_admin',0);
                    } else {
                        $query->where('is_admin',0);
                    }
                }
            })
            ->where('is_active', 1)
            ->orderBy('title')
             ->get();
             $view->with('sequence', $sequence_menu);
         });

         View::composer('*', function($view)
         {
             $icons = Icon::all();
             $view->with('icons', $icons);
         });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for ('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

}