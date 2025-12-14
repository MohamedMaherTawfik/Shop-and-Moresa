<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssociationAdminDashboard extends Controller
{
    public function dashboard()
    {
        return view('web.association-admin.dashboard');
    }
}