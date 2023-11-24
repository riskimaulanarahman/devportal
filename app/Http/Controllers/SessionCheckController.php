<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionCheckController extends Controller
{

    public function checkSession()
    {
        if (auth()->check()) {
            // User telah login, kirim response dengan status success
            return response()->json(['status' => 'success']);
        } else {
            // User belum login, kirim response dengan status expired
            return response()->json(['status' => 'expired']);
        }
    }

}
