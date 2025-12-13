@extends('layouts.admin.app')

@section('title', translate('Association_Coupons'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img width="20" src="{{ theme_asset('assets/img/admin/association-coupons.png') }}" alt="">
            {{ translate('Association_Coupons') }}
        </h2>
    </div>
    <!-- End Page Header -->

    <!-- Card -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h5 class="card-title mb-0">{{ translate('Coupons_List') }}</h5>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.association-coupons.create') }}" class="btn btn-primary">
                            <i class="tio-add"></i> {{ translate('Add_New') }}
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="tio-add"></i> {{ translate('Generate_For_Association') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-body">
            <form method="GET" action="{{ route('admin.association-coupons.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="association_id" class="form-control">
                            <option value="">{{ translate('All_Associations') }}</option>
                            @foreach($associations as $association)
                                <option value="{{ $association->id }}" {{ request('association_id') == $association->id ? 'selected' : '' }}>
                                    {{ $association->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-control">
                            <option value="">{{ translate('All_Status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                            <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>{{ translate('Used') }}</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ translate('Expired') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                        <a href="{{ route('admin.association-coupons.index') }}" class="btn btn-secondary">{{ translate('Clear') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ translate('Code') }}</th>
                        <th>{{ translate('Association') }}</th>
                        <th>{{ translate('User') }}</th>
                        <th>{{ translate('Amount') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Expires_At') }}</th>
                        <th>{{ translate('Used_At') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <span class="badge badge-info">{{ $coupon->code }}</span>
                            </td>
                            <td>{{ $coupon->association->name ?? '-' }}</td>
                            <td>{{ $coupon->user->name ?? '-' }}</td>
                            <td>{{ number_format($coupon->amount, 2) }} {{ translate('SAR') }}</td>
                            <td>
                                @if($coupon->status === 'active')
                                    <span class="badge badge-success">{{ translate('Active') }}</span>
                                @elseif($coupon->status === 'used')
                                    <span class="badge badge-warning">{{ translate('Used') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ translate('Expired') }}</span>
                                @endif
                            </td>
                            <td>{{ $coupon->expires_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $coupon->used_at ? $coupon->used_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($coupon->status === 'active' && $coupon->user_id)
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="redeemCoupon('{{ $coupon->code }}')">
                                            {{ translate('Redeem') }}
                                        </button>
                                    @endif

                                    @if($coupon->status !== 'used')
                                        <form method="POST" action="{{ route('admin.association-coupons.destroy', $coupon) }}"
                                              onsubmit="return confirm('{{ translate('Are_you_sure') }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                {{ translate('Delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ translate('No_coupons_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($coupons->hasPages())
            <div class="card-footer">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.association-coupons.generate') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Generate_Coupons_For_Association') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Association') }} <span class="text-danger">*</span></label>
                        <select name="association_id" class="form-control" required>
                            <option value="">{{ translate('Select_Association') }}</option>
                            @foreach($associations as $association)
                                <option value="{{ $association->id }}">{{ $association->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Expires_At') }} <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="expires_at" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Generate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Redeem Form -->
<form id="redeemForm" method="POST" action="{{ route('admin.association-coupons.redeem') }}" style="display: none;">
    @csrf
    <input type="hidden" name="coupon_code" id="redeemCouponCode">
</form>

<script>
function redeemCoupon(code) {
    if (confirm('{{ translate('Are_you_sure_you_want_to_redeem_this_coupon') }}?')) {
        document.getElementById('redeemCouponCode').value = code;
        document.getElementById('redeemForm').submit();
    }
}
</script>
@endsection



