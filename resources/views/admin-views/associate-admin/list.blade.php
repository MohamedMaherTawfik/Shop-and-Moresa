@extends('layouts.admin.app')

@section('title', 'Associate Admin List')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Associate Admins
        </h1>

        <a href="{{ route('admin.admin-add-new') }}"
           class="inline-flex items-center px-5 py-2 text-sm font-medium text-white
                  bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            + Add New
        </a>
    </div>

   {{-- Table --}}
<div class="w-full p-4 bg-white shadow rounded-lg overflow-x-auto">
    <table class="w-full min-w-full border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">First Name</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Last Name</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Association ID</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>

                    <td class="px-4 py-3">
                        @if($user->image)
                            <img src="{{ asset('storage/'.$user->image) }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-xs">
                                N/A
                            </div>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm">{{ $user->name?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $user->f_name?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $user->l_name?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $user->phone?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $user->email?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $user->association->name?? '-' }}</td>

                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="{{ route('admin.admin-update-view', $user->id) }}"
                           class="px-3 py-1 text-xs text-white bg-yellow-500 rounded hover:bg-yellow-600">
                            Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                        No associate admins found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


</div>
@endsection
