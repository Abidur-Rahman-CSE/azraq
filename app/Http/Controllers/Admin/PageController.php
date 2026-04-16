<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(20);

        return view('admin.content.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.content.pages.create', ['page' => new Page(['is_published' => true])]);
    }

    public function store(PageRequest $request)
    {
        Page::create($request->validated() + [
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.content.pages.index')->with('status', 'Page created.');
    }

    public function edit(Page $page)
    {
        return view('admin.content.pages.edit', compact('page'));
    }

    public function update(PageRequest $request, Page $page)
    {
        $page->update($request->validated() + [
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.content.pages.index')->with('status', 'Page updated.');
    }
}
