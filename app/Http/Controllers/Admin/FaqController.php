<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->paginate(20);

        return view('admin.content.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.content.faqs.create', ['faq' => new Faq(['is_published' => true])]);
    }

    public function store(FaqRequest $request)
    {
        Faq::create($request->validated() + [
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.content.faqs.index')->with('status', 'FAQ created.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.content.faqs.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated() + [
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.content.faqs.index')->with('status', 'FAQ updated.');
    }
}
