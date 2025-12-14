@extends('layouts.admin.app')

@section('title', 'Associate Admin List')

@section('content')
<div class="container-fluid p-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-dark">Associate Admins</h1>

        <a href="{{ route('admin.admin-add-new') }}" class="btn btn-primary">
            + Add New
        </a>
    </div>

    {{-- Table --}}
    <div class="table-responsive bg-white shadow rounded p-3">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>id</th>
                    <th>Name</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Association</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>

                        <td>{{ $user->name ?? '-' }}</td>
                        <td>{{ $user->f_name ?? '-' }}</td>
                        <td>{{ $user->l_name ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>{{ $user->association->name ?? '-' }}</td>

                        <td class="text-center d-flex justify-content-center gap-2">

                           {{-- Status Button --}}
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <form action="{{ route('admin.admin-status', $user->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="is_active" value="1">
                                            <button type="submit" class="dropdown-item">Active</button>
                                        </form>
                                    </li>

                                    <li>
                                        <form action="{{ route('admin.admin-status', $user->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="is_active" value="0">
                                            <button type="submit" class="dropdown-item">Inactive</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No associate admins found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
