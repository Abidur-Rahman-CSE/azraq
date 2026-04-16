<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CollectionRequest;
use App\Models\Collection;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::orderBy('sort_order')->orderBy('name')->paginate(12);

        return view('admin.catalog.collections.index', compact('collections'));
    }

    public function create()
    {
        $collection = new Collection(['is_active' => true]);

        return view('admin.catalog.collections.create', compact('collection'));
    }

    public function store(CollectionRequest $request)
    {
        Collection::create($request->validated() + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection created.');
    }

    public function edit(Collection $collection)
    {
        return view('admin.catalog.collections.edit', compact('collection'));
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $collection->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection updated.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection deleted.');
    }
}
