<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssociationCoupon;
use App\Models\Association;
use App\Models\User;
use App\Utils\CustomerManager;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class AssociationCouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = AssociationCoupon::with(['association', 'user']);

        if ($request->has('association_id') && $request->association_id) {
            $query->where('association_id', $request->association_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(20);
        $associations = Association::where('is_active', true)->get();

        return view('admin-views.association-coupons.index', compact('coupons', 'associations'));
    }

    public function create(): View
    {
        $associations = Association::where('is_active', true)->get();
        $users = collect(); // Empty collection initially
        return view('admin-views.association-coupons.create', compact('associations', 'users'));
    }

    public function getUsers(Request $request): JsonResponse
    {
        $associationId = $request->get('association_id');

        if (!$associationId) {
            return response()->json(['users' => []]);
        }

        $users = \App\Models\User::where('association_id', $associationId)
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['users' => $users]);
    }

    public function store(Request $request): RedirectResponse
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
        return redirect()->route('admin.association-coupons.index');
    }

    public function generateForAssociation(Request $request): RedirectResponse
    {
        $request->validate([
            'association_id' => 'required|exists:associations,id',
            'amount' => 'required|numeric|min:0.01',
            'expires_at' => 'required|date|after:now',
        ]);

        $association = Association::findOrFail($request->association_id);
        $users = User::where('association_id', $association->id)->get();

        if ($users->isEmpty()) {
            ToastMagic::error(translate('No_users_found_for_this_association'));
            return back();
        }

        $coupons = [];
        foreach ($users as $user) {
            $coupons[] = [
                'code' => $this->generateUniqueCode(),
                'association_id' => $association->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'expires_at' => $request->expires_at,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        AssociationCoupon::insert($coupons);

        ToastMagic::success(translate('Coupons_generated_for_all_association_users'));
        return redirect()->route('admin.association-coupons.index');
    }

    public function destroy(AssociationCoupon $coupon): RedirectResponse
    {
        if ($coupon->status === 'used') {
            ToastMagic::error(translate('Cannot_delete_used_coupon'));
            return back();
        }

        $coupon->delete();
        ToastMagic::success(translate('Coupon_deleted_successfully'));
        return redirect()->route('admin.association-coupons.index');
    }

public function redeem(Request $request): RedirectResponse
{
    $request->validate([
        'coupon_code' => 'required|string',
    ]);

    $coupon = AssociationCoupon::where('code', $request->coupon_code)
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->first();

    if (!$coupon) {
        ToastMagic::error(translate('Invalid_or_expired_coupon'));
        return back();
    }

    if (!$coupon->user_id) {
        ToastMagic::error(translate('Coupon_not_assigned_to_user'));
        return back();
    }

    // بدء transaction لضمان atomicity
    \DB::beginTransaction();

    try {
        // الحصول على آخر رصيد للمستخدم
        $lastBalance = \DB::table('wallet_transactions')
            ->where('user_id', $coupon->user_id)
            ->orderByDesc('id')
            ->value('balance') ?? 0;

        $newBalance = $lastBalance + $coupon->amount;

        // إنشاء معاملة المحفظة مباشرة
        $walletTransactionId = \DB::table('wallet_transactions')->insertGetId([
            'user_id' => $coupon->user_id,
            'transaction_id' => (string) \Str::uuid(),
            'credit' => $coupon->amount,
            'debit' => 0,
            'admin_bonus' => 0,
            'balance' => $newBalance,
            'transaction_type' => 'coupon_redeem',
            'payment_method' => 'coupon',
            'reference' => "Redeemed coupon: {$coupon->code}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تحديث حالة الكوبون
        $coupon->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        // إنشاء سجل المعاملة في جدول coupon_transactions
        \App\Models\CouponTransaction::create([
            'coupon_id' => $coupon->id,
            'user_id' => $coupon->user_id,
            'wallet_transaction_id' => $walletTransactionId,
            'amount' => $coupon->amount,
            'status' => 'completed',
        ]);

        \DB::commit();
        ToastMagic::success(translate('Coupon_redeemed_successfully'));

    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('Failed to redeem coupon: ' . $e->getMessage());
        ToastMagic::error(translate('Failed_to_redeem_coupon'));
    }

    return back();
}

    private function generateUniqueCode(): string
    {
        do {
            $code = 'COUPON' . strtoupper(Str::random(8));
        } while (AssociationCoupon::where('code', $code)->exists());

        return $code;
    }
}
