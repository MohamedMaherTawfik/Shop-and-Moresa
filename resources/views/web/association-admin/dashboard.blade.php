@extends('layouts.admin.app2')

@section('title', 'Association Dashboard')

@section('content')
<div class="container-fluid mt-4">

    {{-- Association Name --}}
    <div class="mb-4">
        <h3 class="fw-bold text-dark">
            {{ $association->name ?? '-' }}
        </h3>
    </div>

    {{-- Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">{{ translate('benefits') }}</h6>
                        <h3 class="fw-bold mb-0">{{ $users->count() }}</h3>
                    </div>
                    <div class="fs-1 text-primary">
                        <i class="fi fi-rr-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">
            Users List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Wallet Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration ?? '-' }}</td>
                                <td>{{ $user->name ?? '-' }}</td>
                                <td>{{ $user->email ?? '-' }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold">
                                        {{ $user->wallet_balance ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('association-admin.createCoupon', $user) }}"
                                       class="btn btn-sm btn-primary">
                                        Add Coupon
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
