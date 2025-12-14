<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssociationAdminAuth extends Controller
{
    public function login()
    {
        return view('web.association-admin.login');
    }

    public function login_submit(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if(Auth::attempt($credentials))
        {
            if(Auth::user()->role == 'association-admin'){
            request()->session()->regenerate();
            return redirect()->route('association-admin.dashboard');
            }
        }
        return redirect()->back()->withInput($request->only('email'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
