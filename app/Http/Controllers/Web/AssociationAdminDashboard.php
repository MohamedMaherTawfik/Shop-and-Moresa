<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\AssociationCoupon;
use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function createCoupon(User $user)
    {
        $association=Association::where('id', auth()->user()->association_id)->first();
        return view('web.association-admin.create-coupon',compact('user','association'));
    }

    public function storeCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'association_id' => 'required|exists:associations,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'expires_at' => 'required|date|after:now',
            'count' => 'required|integer|min:1|max:1000',
        ]);
        $coupons = [];
        for ($i = 0; $i < $request->count; $i++) {
            $coupons[] = [
                'code' => $this->generateUniqueCode(),
                'association_id' => $request->association_id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'expires_at' => $request->expires_at,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        AssociationCoupon::insert($coupons);

        ToastMagic::success(translate('Coupons_created_successfully'));
        return redirect()->route('association-admin.dashboard');
    }

      private function generateUniqueCode(): string
    {
        do {
            $code = 'COUPON' . strtoupper(Str::random(8));
        } while (AssociationCoupon::where('code', $code)->exists());

        return $code;
    }
}
