<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\AssociationCoupon;
use App\Models\CouponTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Utils\CartManager;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Utils\OrderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CouponController extends Controller
{
    public function list(Request $request)
    {
        $customer_id = $request->user() ? $request->user()->id : '0';

        $coupons = Coupon::with('seller.shop')
            ->withCount(['order' => function ($query) use ($customer_id) {
                $query->where(['customer_id' => $customer_id]);
            }])
            ->where(['status' => 1])
            ->whereIn('customer_id', [$customer_id, '0'])
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())
            ->select('coupons.*', DB::raw('DATE(expire_date) as plain_expire_date'))
            ->inRandomOrder()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        return [
            'total_size' => $coupons->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'coupons' => $coupons->items()
        ];
    }

    public function applicable_list(Request $request): JsonResponse
    {

        $customer_id = $request->user() ? $request->user()->id : '0';

        $cart_data = Cart::where(['customer_id' => $customer_id, 'is_guest' => 0, 'is_checked' => 1])->pluck('product_id');
        $productGroup = Product::whereIn('id', $cart_data)->select('id', 'added_by', 'user_id')->get();

        if ($cart_data->count() > 0 && $productGroup->count() > 0) {
            $couponQuery = Coupon::active()
            ->with('seller.shop')
                ->select('coupons.*', DB::raw('DATE(expire_date) as plain_expire_date'))
                ->withCount(['order' => function ($query) use ($customer_id) {
                    $query->where('customer_id', $customer_id);
                }])
                ->whereIn('customer_id', [$customer_id, '0'])
                ->whereDate('start_date', '<=', now())
                ->whereDate('expire_date', '>=', now())
                ->get();

            $adminCoupon = null;
            $vendorCoupon = null;

            if ($productGroup->where('added_by', 'admin')->count() > 0) {
                $adminCoupon = $couponQuery->where('coupon_bearer', 'inhouse');
            }

            if ($productGroup->where('added_by', 'seller')->count() > 0) {
                $sellerIds = $productGroup->pluck('seller_id')->unique()->toArray();
                $vendorCoupon = $couponQuery->where('coupon_bearer', 'seller')->whereIn('seller_id', $sellerIds)->where('added_by', 'seller');
            }

            $coupons = collect();
            if ($adminCoupon) {
                $coupons = $coupons->merge($adminCoupon);
            }

            if ($vendorCoupon) {
                $coupons = $coupons->merge($vendorCoupon);
            }

            $coupons = $coupons->filter(function ($data) {
                return (($data->order_count < $data->limit) || empty($data->limit)) && ($data->start_date <= now() && $data->expire_date >= now());
            })->values();

            $customer_order_count = Order::where('customer_id', $customer_id)->count();
            if ($customer_order_count > 0) {
                $coupons = $coupons->whereNotIn('coupon_type', ['first_order']);
            }
        }
        return response()->json($coupons ?? [], 200);
    }

    public function apply(Request $request): JsonResponse
    {
        $result = OrderManager::getTotalCouponAmount(request: $request, couponCode: $request['code']);
        if ($result['status']) {
            return response()->json([
                'coupon_discount' => $result['discount'],
                'coupon_type' => $result['coupon_type']
            ], 200);
        }
        return response()->json(translate('invalid_coupon'), 202);
    }

    public function getSellerWiseCoupon(Request $request, $seller_id): array
    {
        $customerId = $request->user() ? $request->user()->id : '0';

        $sellerIds = ['0'];
        $coupons = Coupon::with('seller.shop')
            ->where(['status' => 1])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->when($seller_id == '0', function ($query) use ($sellerIds) {
                return $query->whereNull('seller_id');
            })
            ->when($seller_id != '0', function ($query) use ($sellerIds, $seller_id) {
                $sellerIds[] = $seller_id;
                return $query->whereIn('seller_id', $sellerIds);
            })
            ->when($customerId == '0', function ($query) {
                return $query->where('customer_id', 0);
            })
            ->select('coupons.*', DB::raw('DATE(expire_date) as plain_expire_date'))
            ->inRandomOrder()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        return [
            'total_size' => $coupons->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'coupons' => $coupons->items()
        ];
    }
  
    /**
     * Get association coupons for authenticated user
     */
    public function getAssociationCoupons(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $coupons = AssociationCoupon::with(['association', 'user'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->get();

            $totalAmount = $coupons->sum('amount');
            $usedAmount = AssociationCoupon::where('user_id', $user->id)
                ->where('status', 'used')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'coupons' => $coupons,
                    'summary' => [
                        'total_coupons' => $coupons->count(),
                        'total_amount' => $totalAmount,
                        'used_amount' => $usedAmount,
                        'remaining_amount' => $totalAmount - $usedAmount
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting association coupons: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching coupons'
            ], 500);
        }
    }

    /**
     * Redeem association coupon
     */
    public function redeemAssociationCoupon(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'coupon_code' => 'required|string',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $coupon = AssociationCoupon::where('code', $request->coupon_code)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon'
                ], 400);
            }

            if (!$coupon->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not assigned to user'
                ], 400);
            }

            if ($coupon->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not assigned to you'
                ], 400);
            }

            DB::beginTransaction();

            // Check if wallet is enabled
            $walletStatus = \App\Models\BusinessSetting::where('type', 'wallet_status')->first();
            if (!$walletStatus || $walletStatus->value != 1) {
                throw new \Exception('Wallet is disabled');
            }

            // Create wallet transaction
            $walletTransaction = CustomerManager::create_wallet_transaction(
                $coupon->user_id,
                $coupon->amount,
                'coupon_redeem',
                "Coupon: {$coupon->code}",
                []
            );

            if (!$walletTransaction || $walletTransaction === true) {
                throw new \Exception('Failed to create wallet transaction');
            }

            // Update coupon status
            $coupon->update([
                'status' => 'used',
                'used_at' => now()
            ]);

            // Create coupon transaction record
            CouponTransaction::create([
                'coupon_id' => $coupon->id,
                'user_id' => $coupon->user_id,
                'wallet_transaction_id' => $walletTransaction->id,
                'amount' => $coupon->amount,
                'status' => 'completed'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coupon redeemed successfully',
                'data' => [
                    'coupon' => $coupon,
                    'wallet_transaction' => $walletTransaction,
                    'amount_added' => $coupon->amount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Coupon redemption error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while redeeming coupon: ' . $e->getMessage()
            ], 500);
        }
    }
}
