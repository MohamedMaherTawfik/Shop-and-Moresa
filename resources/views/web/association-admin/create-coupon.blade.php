@extends('layouts.admin.app2')

@section('title', 'Association Dashboard')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img width="20" src="{{ theme_asset('assets/img/admin/association-coupons.png') }}" alt="">
            {{ translate('Create_Association_Coupons') }}
        </h2>
    </div>
    <!-- End Page Header -->

    <!-- Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('Coupon_Details') }}</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="{{ route('association-admin.storeCoupon', $user) }}">
            @csrf

            {{-- Hidden Inputs --}}
            <input type="hidden" name="association_id" value="{{ $association->id }}">
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="row">

                {{-- Association (Display Only) --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Association') }}</label>
                        <input type="text" class="form-control" value="{{ $association->name }}" disabled>
                    </div>
                </div>

                {{-- User (Display Only) --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('User') }}</label>
                        <input type="text" class="form-control"
                            value="{{ $user->name }} ({{ $user->email }})"
                            disabled>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount"
                            class="form-control @error('amount') is-invalid @enderror"
                            step="0.01" min="0.01" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Count --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Count') }} <span class="text-danger">*</span></label>
                        <input type="number" name="count"
                            class="form-control @error('count') is-invalid @enderror"
                            min="1" max="1000" value="1" required>
                        @error('count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Expires At --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Expires_At') }} <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="expires_at"
                            class="form-control @error('expires_at') is-invalid @enderror"
                            required>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="tio-save"></i> {{ translate('Create_Coupons') }}
                </button>

                <a href="{{ route('association-admin.dashboard') }}" class="btn btn-secondary">
                    <i class="tio-clear"></i> {{ translate('Cancel') }}
                </a>
            </div>
        </form>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('#association_id').on('change', function() {
        var associationId = $(this).val();
        var userSelect = $('#user_id');

        // Clear current options
        userSelect.html('<option value="">{{ translate("Select_User") }}</option>');

        if (associationId) {
            // Show loading
            userSelect.prop('disabled', true);
            userSelect.html('<option value="">{{ translate("Loading") }}...</option>');

            // Fetch users for this association
            $.ajax({
                url: '{{ route("admin.association-coupons.get-users") }}',
                method: 'GET',
                data: { association_id: associationId },
                success: function(response) {
                    console.log('AJAX Success:', response); // Debug log
                    userSelect.html('<option value="">{{ translate("Select_User") }}</option>');

                    if (response.users && response.users.length > 0) {
                        $.each(response.users, function(index, user) {
                            userSelect.append('<option value="' + user.id + '">' + user.name + ' (' + user.email + ')</option>');
                        });
                    } else {
                        userSelect.append('<option value="">{{ translate("No_Users_Found") }}</option>');
                    }

                    userSelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText, status, error); // Debug log
                    userSelect.html('<option value="">{{ translate("Error_Loading_Users") }}</option>');
                    userSelect.prop('disabled', false);
                }
            });
        } else {
            userSelect.prop('disabled', true);
        }
    });
});
</script>
@endpush
