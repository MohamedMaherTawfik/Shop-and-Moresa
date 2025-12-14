@extends('layouts.admin.app')

@section('title', translate('Add Associate Admin'))

@section('content')
<div class="container-fluid p-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-dark">Add Associate Admin</h1>
        <a href="{{ route('admin.admin-list') }}" class="btn btn-secondary">
            Back to List
        </a>
    </div>

    {{-- Form --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.admin-add-new-post') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="f_name" class="form-label">First Name</label>
                        <input type="text" name="f_name" id="f_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="l_name" class="form-label">Last Name</label>
                        <input type="text" name="l_name" id="l_name" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="association_id" class="form-label">Association</label>
                        <select name="association_id" id="association_id" class="form-select" required>
                            <option value="">Select Association</option>
                            @foreach($associatives as $association)
                                <option value="{{ $association->id }}">{{ $association->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Profile Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Add Associate Admin</button>
            </form>
        </div>
    </div>

</div>
@endsection
