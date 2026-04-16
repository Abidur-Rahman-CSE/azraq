<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSectionRequest;
use App\Models\HomepageSection;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();

        return view('admin.content.homepage-sections.index', compact('sections'));
    }

    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.content.homepage-sections.edit', ['section' => $homepageSection]);
    }

    public function update(HomepageSectionRequest $request, HomepageSection $homepageSection)
    {
        $homepageSection->update($request->validated() + [
            'is_enabled' => $request->boolean('is_enabled'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.content.homepage-sections.index')->with('status', 'Homepage section updated.');
    }
}
