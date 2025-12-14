<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AssociationAdminDashboard extends Controller
{
    public function dashboard()
    {
        $users=User::where('association_id',auth()->user()->association_id)->get();
        dd($users);
        return view('web.association-admin.dashboard',compact('users'));
    }
}
