<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AssociationController extends Controller
{
	public function getAssociations()
	{
		$associations = Association::all()->map(function ($association) {
			return [
				'id' => $association->id,
				'name' => $association->name,
				'slug' => $association->slug,
				'description' => $association->description,
				'image' => $association->image ? url($association->image) : null,
				'url' => $association->url,
				'is_active' => $association->is_active,
				'priority' => $association->priority,
				'created_at' => $association->created_at,
				'updated_at' => $association->updated_at,
			];
		});

		return response()->json([
			'status' => 'success',
			'data' => $associations
		], 200);
	}


	public function index(Request $request): View
	{
		$items = Association::orderBy('priority')->paginate(20);
		return view('admin-views.associations.index', compact('items'));
	}

	public function create(): View
	{
		return view('admin-views.associations.create');
	}

    public function store(Request $request): RedirectResponse
	{
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'slug' => ['nullable', 'string', 'max:255'],
			'url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'string', 'max:512'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
			'description' => ['nullable', 'string'],
			'is_active' => ['nullable', 'boolean'],
			'priority' => ['nullable', 'integer', 'min:0'],
		]);

		$validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
		$validated['is_active'] = (bool)($validated['is_active'] ?? true);
		$validated['priority'] = (int)($validated['priority'] ?? 0);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('associations', 'public');
            // store display-friendly path under public storage symlink
            $validated['image'] = 'storage/' . ltrim($path, '/');
        }

		Association::create($validated);
		return redirect()->route('admin.associations.index')->with('success', translate('Association created successfully'));
	}

	public function edit(Association $association): View
	{
		return view('admin-views.associations.edit', ['item' => $association]);
	}

    public function update(Request $request, Association $association): RedirectResponse
	{
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'slug' => ['nullable', 'string', 'max:255'],
			'url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'string', 'max:512'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
			'description' => ['nullable', 'string'],
			'is_active' => ['nullable', 'boolean'],
			'priority' => ['nullable', 'integer', 'min:0'],
		]);

		$validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
		$validated['is_active'] = (bool)($validated['is_active'] ?? $association->is_active);
		$validated['priority'] = (int)($validated['priority'] ?? $association->priority);
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('associations', 'public');
            $validated['image'] = 'storage/' . ltrim($path, '/');
        }

		$association->update($validated);
		return redirect()->route('admin.associations.index')->with('success', translate('Association updated successfully'));
	}

	public function destroy(Association $association): RedirectResponse
	{
		$association->delete();
		return redirect()->route('admin.associations.index')->with('success', translate('Association deleted successfully'));
	}
}


