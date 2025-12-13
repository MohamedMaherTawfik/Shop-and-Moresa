<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\AssociationCoupon;
use App\Models\CouponTransaction;
use App\Utils\Helpers;
use App\Utils\CustomerManager;
use App\Models\AddFundBonusCategories;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function App\Utils\payment_gateways;

class UserWalletController extends Controller
{

    public function index(Request $request): View|RedirectResponse
    {
        $walletStatus = getWebConfig(name: 'wallet_status');
        if ($walletStatus == 1) {
            $transactionTypes = $this->getSelectTransactionTypes(types: $request->get('types', []));
            $totalWalletBalance = auth('customer')->user()->wallet_balance;

            $walletTransactionList = $this->getWalletTransactionList(request: $request, types: $transactionTypes);
            $paymentGatewayList = payment_gateways();
            $addFundBonusList = $this->getAddFundBonusList();

            $filterCount = count($request['types']??[]) + (int)!empty($request['transaction_range']) + (int)!empty($request['filter_by']);

            if ($request->has('flag') && $request['flag'] == 'success') {
                Toastr::success(translate('add_fund_to_wallet_success'));
                return redirect()->route('wallet');
            } else if ($request->has('flag') && $request['flag'] == 'fail') {
                Toastr::error(translate('add_fund_to_wallet_unsuccessful'));
                return redirect()->route('wallet');
            }

            $digitalPaymentStatus = getWebConfig(name: 'digital_payment');
            $addFundsToWallet = getWebConfig(name: 'add_funds_to_wallet');
            $addFundsToWalletStatus = $addFundsToWallet && count($paymentGatewayList) > 0 && ($digitalPaymentStatus['status'] ?? 0);

            return view(VIEW_FILE_NAMES['user_wallet'], [
                'addFundsToWalletStatus' => $addFundsToWalletStatus,
                'totalWalletBalance' => $totalWalletBalance,
                'walletTransactionList' => $walletTransactionList,
                'paymentGatewayList' => $paymentGatewayList,
                'addFundBonusList' => $addFundBonusList,
                'transactionTypes' => $request->get('types', []),
                'filterCount' => $filterCount,
                'filterBy' => $request['filter_by'] ?? '',
                'transactionRange' => $request['transaction_range'] ?? '',
            ]);

        } else {
            Toastr::warning(translate('access_denied!'));
            return redirect()->route('home');
        }
    }

    public function myWalletAccount(): View
    {
        return view(VIEW_FILE_NAMES['wallet_account']);
    }

       public function redeemCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $coupon = AssociationCoupon::where('code', $request->coupon_code)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$coupon) {
            Toastr::error(translate('Invalid_or_expired_coupon'));
            return back();
        }

        if (!$coupon->user_id) {
            Toastr::error(translate('Coupon_not_assigned_to_user'));
            return back();
        }

        // Check if coupon is assigned to current user
        if ($coupon->user_id != auth('customer')->id()) {
            Toastr::error(translate('Coupon_not_assigned_to_you'));
            return back();
        }

        try {
            DB::beginTransaction();

            // Check if wallet is enabled
            $walletStatus = \App\Models\BusinessSetting::where('type', 'wallet_status')->first();
            if (!$walletStatus || $walletStatus->value != 1) {
                throw new \Exception('Wallet is disabled');
            }

            // Check if user exists
            $user = \App\User::find($coupon->user_id);
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Create wallet transaction
            $walletTransaction = CustomerManager::create_wallet_transaction(
                $coupon->user_id,
                $coupon->amount,
                'coupon_redeem',
                "Coupon: {$coupon->code}",
                []
            );

            Log::info('Wallet transaction result: ' . json_encode($walletTransaction));
            Log::info('Wallet transaction type: ' . gettype($walletTransaction));

            if (!$walletTransaction || $walletTransaction === true) {
                throw new \Exception('Failed to create wallet transaction - returned: ' . json_encode($walletTransaction));
            }

            if (!isset($walletTransaction->id)) {
                throw new \Exception('Wallet transaction object does not have id property');
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
            Toastr::success(translate('Coupon_redeemed_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Coupon redemption error: ' . $e->getMessage());
            Log::error('Coupon redemption error details: ' . $e->getTraceAsString());
            Toastr::error(translate('An_error_occurred_while_redeeming_coupon') . ': ' . $e->getMessage());
        }

        return redirect()->route('wallet');
    }

    private function getWalletTransactionList(object|array $request, array $types)
    {
        $startDate = '';
        $endDate = '';
        if (isset($request['transaction_range']) && !empty($request['transaction_range'])) {
            $dates = explode(' - ', $request['transaction_range']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d') . ' 00:00:00';
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d') . ' 23:59:59';
        }
        return WalletTransaction::where('user_id', auth('customer')->id())
            ->when($request->has('filter_by') && in_array($request['filter_by'], ['debit', 'credit']), function ($query) use ($request) {
                $query->when($request['filter_by'] == 'debit', function ($query) {
                    $query->where('debit', '!=', 0);
                })->when($request['filter_by'] == 'credit', function ($query) {
                    $query->where('debit', '=', 0);
                });
            })
            ->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($types) || in_array('added_via_payment_method', $request['types'] ?? []) || in_array('earned_by_referral', $request['types'] ?? []), function ($query) use ($types, $request) {
                $query->where(function ($query) use ($types, $request) {
                    return $query->when(!empty($types), function ($query) use ($types, $request) {
                        return $query->when(!in_array('earned_by_referral', $types), function ($query) use ($types) {
                                return $query->where('reference', '!=', 'earned_by_referral');
                            })->whereIn('transaction_type', $types)
                            ->orWhere(function ($query) use ($types, $request) {
                                return $query->whereNull('reference');
                            });
                    })->when(in_array('added_via_payment_method', $request['types'] ?? []), function ($query) use ($types, $request) {
                        return $query->orWhere('reference', 'add_funds_to_wallet');
                    })->when(in_array('earned_by_referral', $request['types'] ?? []), function ($query) use ($types, $request) {
                       return $query->orWhere('reference', 'earned_by_referral');
                    });
                });
            })
            ->latest()
            ->paginate(10)->appends(request()->query());
    }

    public function getAddFundBonusList()
    {
        return AddFundBonusCategories::where('is_active', 1)
            ->whereDate('start_date_time', '<=', date('Y-m-d'))
            ->whereDate('end_date_time', '>=', date('Y-m-d'))
            ->get();
    }

    public function getSelectTransactionTypes($types): array
    {
        $typeMapping = [
            'order_refund' => 'order_refund',
            'order_place' => 'order_place',
            'loyalty_point' => 'loyalty_point',
            'add_fund' => 'add_fund',
            'add_fund_by_admin' => 'add_fund_by_admin',
            'coupon_redeem' => 'coupon_redeem',
        ];

        foreach ($typeMapping as $key => $value) {
            if (in_array($key, $types)) {
                $transactionTypes[] = $value;
            }
        }
        return $transactionTypes ?? [];
    }
}
