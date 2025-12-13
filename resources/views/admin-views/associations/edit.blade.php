@extends('layouts.admin.app')

@section('title', translate('Edit Association'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">{{ translate('Edit Association') }}</h2>
            <a href="{{ route('admin.associations.index') }}" class="btn btn-secondary btn-outline-secondary">{{ translate('Back') }}</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.associations.update', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Name') }}</label>
                                <input type="text" name="name" class="form-control" required value="{{ $item->name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Slug') }}</label>
                                <input type="text" name="slug" class="form-control" value="{{ $item->slug }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('URL') }}</label>
                                <input type="url" name="url" class="form-control" value="{{ $item->url }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Image') }}</label>
                                <input type="file" name="image_file" class="form-control" accept="image/*">
                                <small class="text-muted d-block">{{ translate('Current') }}: {{ $item->image }}</small>
                                <input type="text" name="image" class="form-control mt-1" value="{{ $item->image }}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3">{{ $item->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Priority') }}</label>
                                <input type="number" name="priority" class="form-control" value="{{ $item->priority }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-group mb-0">
                                <label class="checkbox">
                                    <input type="checkbox" name="is_active" value="1" @if($item->is_active) checked @endif>
                                    <span>{{ translate('Active') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn--primary btn-primary">{{ translate('Save changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


