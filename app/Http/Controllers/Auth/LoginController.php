<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Symfony\Component\HttpFoundation\Request;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\ProcessProjectTrait;


class LoginController extends Controller
{
    use AuthenticatesUsers, ListensForLdapBindFailure, ProcessProjectTrait;

    protected $redirectTo = '/';

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        //
    }

    protected function credentials(Request $request)
    {

        return [
            'samaccountname' => $request->username,
            'password' => $request->password,
            'fallback' => [
                'email' => $request->email,
                'password' => $request->password,
            ],
        ];

    }

    public function showLoginForm()
    {
        return view('auth.login')->with('project', $this->processProjects());
    }

    // public function logout(Request $request)
    // {
    //     // Clear session data from the database
    //     DB::table('sessions')->where('user_id', auth()->id())->delete();

    //     // Log out the user
    //     $this->guard()->logout();

    //     $request->session()->invalidate();

    //     return redirect()->intended($this->redirectTo);

    // }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->listenForLdapBindFailure();
    }

}