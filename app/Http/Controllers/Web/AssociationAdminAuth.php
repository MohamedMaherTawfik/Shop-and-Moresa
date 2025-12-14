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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt($request->only('email', 'password'))) {

            if (Auth::user()->role === 'associate-admin') {
                $request->session()->regenerate();
                return redirect()->route('association-admin.dashboard');
            } else {
                Auth::logout();
            }
        }
        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid email or password');
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
