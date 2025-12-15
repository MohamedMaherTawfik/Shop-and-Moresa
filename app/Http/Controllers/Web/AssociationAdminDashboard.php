<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\User;
use Illuminate\Http\Request;

class AssociationAdminDashboard extends Controller
{
    public function dashboard()
    {
       $users = User::where('association_id', auth()->user()->association_id)
            ->where('id', '!=', auth()->id())
            ->get();

            $association=Association::where('id', auth()->user()->association_id)->first();
        return view('web.association-admin.dashboard',compact('users','association'));
    }
}
