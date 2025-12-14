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

    /** صفحة التعديل */
    public function getUpdateView($id)
    {
        $user = User::findOrFail($id);
        return view('admin-views.associate-admin.update', compact('user'));
    }

    /** تحديث البيانات */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'f_name'         => 'required|string|max:255',
            'l_name'         => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'association_id' => 'required|integer',
            'image'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('admin.admin-list')
            ->with('success', 'تم التحديث بنجاح');
    }

    /** تحديث الحالة (active / inactive) */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:users,id',
            'status' => 'required|boolean',
        ]);

        User::where('id', $request->id)
            ->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }
}
