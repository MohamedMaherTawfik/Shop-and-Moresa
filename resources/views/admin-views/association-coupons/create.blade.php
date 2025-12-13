@extends('layouts.admin.app')

@section('title', translate('Create_Association_Coupons'))

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
            <form method="POST" action="{{ route('admin.association-coupons.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Association') }} <span class="text-danger">*</span></label>
                            <select name="association_id" id="association_id" class="form-control @error('association_id') is-invalid @enderror" required>
                                <option value="">{{ translate('Select_Association') }}</option>
                                @foreach($associations as $association)
                                    <option value="{{ $association->id }}" {{ old('association_id') == $association->id ? 'selected' : '' }}>
                                        {{ $association->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('association_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('User') }} <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">{{ translate('Select_User') }}</option>
                                @if(old('association_id'))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   step="0.01" min="0.01" value="{{ old('amount') }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Count') }} <span class="text-danger">*</span></label>
                            <input type="number" name="count" class="form-control @error('count') is-invalid @enderror"
                                   min="1" max="1000" value="{{ old('count', 1) }}" required>
                            <small class="form-text text-muted">{{ translate('Number_of_coupons_to_generate') }}</small>
                            @error('count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Expires_At') }} <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                                   value="{{ old('expires_at') }}" required>
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
                    <a href="{{ route('admin.association-coupons.index') }}" class="btn btn-secondary">
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
