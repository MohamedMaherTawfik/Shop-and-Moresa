@extends('layouts.admin.app')

@section('title', translate('Associations'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">{{ translate('Associations') }}</h2>
            <a href="{{ route('admin.associations.create') }}" class="btn btn--primary btn-primary">{{ translate('Add New') }}</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover table-borderless mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('URL') }}</th>
                        <th>{{ translate('Priority') }}</th>
                        <th>{{ translate('Active') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $k => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $k }}</td>
                            <td>{{ $item->name }}</td>
                            <td><a href="{{ $item->url }}" target="_blank">{{ $item->url }}</a></td>
                            <td>{{ $item->priority }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">{{ translate('Active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ translate('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.associations.edit', $item->id) }}" class="btn btn--primary btn-primary btn-sm">{{ translate('Edit') }}</a>
                                <form action="{{ route('admin.associations.destroy', $item->id) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn--danger btn-danger btn-sm" onclick="return confirm('{{ translate('Are you sure?') }}')">{{ translate('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {!! $items->links() !!}
            </div>
        </div>
    </div>
@endsection


