<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use App\Models\Session;

class SessionCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $getdatasession = Session::where('id',session()->getId())->whereNull('user_id')->count();
        if (auth()->check() && $getdatasession > 0) {
            auth()->logout();
            session()->flash('error','Your session has expired. Please log in again.');
            return redirect()->route('login');
        }
        return $next($request);
    }
}
