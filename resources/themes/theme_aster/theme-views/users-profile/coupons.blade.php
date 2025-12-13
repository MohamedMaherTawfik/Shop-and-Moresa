@php use App\Utils\Helpers; @endphp
@extends('theme-views.layouts.app')
@section('title', translate('coupons').' | '.$web_config['company_name'].' '.translate('ecommerce'))
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h5>{{translate('association_coupons')}}</h5>
                                <a href="{{ route('user-profile') }}"
                                   class="btn-link text-secondary d-flex align-items-baseline">
                                    <i class="bi bi-chevron-left fs-12"></i> {{translate('go_back')}}
                                </a>
                            </div>
                            <div class="mt-4">
                                <div class="row g-3">
                                    @foreach ($coupons as $item)
                                        <div class="col-md-6">
                                            <div class="ticket-box">
                                                <div class="ticket-start">
                                                    {{-- Association Coupon Icon --}}
                                                    <img width="30"
                                                         src="{{ theme_asset('assets/img/icons/dollar.png') }}"
                                                         alt="">
                                                    <h2 class="ticket-amount">
                                                        {{ webCurrencyConverter($item->amount) }}
                                                    </h2>
                                                    <p class="text-capitalize">
                                                        {{ translate('association_coupon') }}
                                                        @if(isset($item->association))
                                                            - {{ $item->association->name ?? translate('association') }}
                                                        @endif
                                                    </p>
                                                    <p class="text-muted small">
                                                        {{ translate('coupon_id') }}: #{{ $item->id }}
                                                    </p>
                                                </div>
                                                <div class="ticket-border"></div>
                                                <div class="ticket-end click-to-copy-code-div">
                                                    <button
                                                        class="ticket-welcome-btn click-to-copy-code"
                                                        data-copy-code="{{ $item->code }}">{{ $item->code }}
                                                    </button>
                                                    <h6>{{ translate('valid_till') }} {{ \Carbon\Carbon::parse($item->expires_at)->format('d M, Y') }}</h6>
                                                    <p class="m-0">
                                                        {{ translate('status') }}:
                                                        <span class="badge badge-{{ $item->status == 'active' ? 'success' : 'danger' }}">
                                                            {{ translate($item->status) }}
                                                        </span>
                                                    </p>
                                                    @if($item->used_at)
                                                        <p class="m-0 text-muted">
                                                            {{ translate('used_at') }}: {{ \Carbon\Carbon::parse($item->used_at)->format('d M, Y H:i') }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(count($coupons) == 0)
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-5 w-100">
                                                <img width="80" class="mb-3" src="{{ theme_asset('assets/img/empty-state/empty-coupon.svg') }}" alt="">
                                                <h5 class="text-center text-muted">
                                                    {{ translate('No_coupon_available') }}!
                                                </h5>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-12 mt-5">
                                        {{ $coupons->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
