<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Http\Request;

class associateAdminController extends Controller
{
    public function index()
    {
        $users = User::where('role','associate-admin')->get();
        return view('admin-views.associate-admin.list', compact('users'));
    }

    /** صفحة الإضافة */
    public function getAddView()
    {
        $associatives=Association::all();
        return view('admin-views.associate-admin.add',compact('associatives'));
    }

    /** حفظ مستخدم جديد */
    public function add(Request $request)
    {
        $data = $request->validate([
            'f_name'         => 'required|string|max:255',
            'l_name'         => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'required|email|unique:users,email',
            'association_id' => 'required|integer',
            'image'          => 'nullable|image|max:2048',
            'password'       => 'required|string|min:6',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('users', 'public');
        }
        $data['name'] = $data['f_name'] . ' ' . $data['l_name'];
        $data['password'] = bcrypt($data['password']);
        User::create([
            'name'           => $data['name'],
            'f_name'         => $data['f_name'],
            'l_name'         => $data['l_name'],
            'phone'          => $data['phone'],
            'email'          => $data['email'],
            'association_id' => $data['association_id'],
            'image'          => $data['image'],
            'password'       => $data['password'],
            'role'           => 'associate-admin',
        ]);

        return redirect()
            ->route('admin.admin-list')
            ->with('success', 'تمت الإضافة بنجاح');
    }

    /** تحديث الحالة (active / inactive) */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        User::where('id', request('id'))
            ->update(['is_active' => $request->is_active]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        User::where('id', $id)->delete();
        return redirect()->route('admin.admin-list')->with('success', 'تمت الحذف بنجاح');
    }
}
