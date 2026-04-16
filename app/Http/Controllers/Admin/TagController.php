<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->paginate(12);

        return view('admin.catalog.tags.index', compact('tags'));
    }

    public function create()
    {
        $tag = new Tag(['is_active' => true]);

        return view('admin.catalog.tags.create', compact('tag'));
    }

    public function store(TagRequest $request)
    {
        Tag::create($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.catalog.tags.index')->with('status', 'Tag created.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.catalog.tags.edit', compact('tag'));
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.catalog.tags.index')->with('status', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.catalog.tags.index')->with('status', 'Tag deleted.');
    }
}
