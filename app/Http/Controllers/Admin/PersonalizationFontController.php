<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalizationFont;
use Illuminate\Http\Request;

class PersonalizationFontController extends Controller
{
    public function index()
    {
        $fonts = PersonalizationFont::whereNull('personalization_template_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $starterPresets = PersonalizationFont::starterPresets();

        return view('admin.personalization.fonts.index', compact('fonts', 'starterPresets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'internal_name'         => ['required', 'string', 'max:255'],
            'css_font_family'       => ['required', 'string', 'max:255'],
            'font_family'           => ['nullable', 'string', 'max:255'],
            'font_source_type'      => ['nullable', 'in:google,local,uploaded'],
            'font_source_value'     => ['nullable', 'string', 'max:2048'],
            'category'              => ['nullable', 'string', 'max:255'],
            'preview_label'         => ['nullable', 'string', 'max:255'],
            'preview_sample_text'   => ['nullable', 'string', 'max:255'],
            'font_weight_default'   => ['nullable', 'string', 'max:10'],
            'font_style_default'    => ['nullable', 'in:normal,italic'],
            'letter_spacing_default'=> ['nullable', 'numeric'],
            'line_height_default'   => ['nullable', 'numeric'],
            'text_transform_default'=> ['nullable', 'in:none,uppercase,lowercase,capitalize'],
            'recommended_for'       => ['nullable', 'string', 'max:255'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        PersonalizationFont::create([
            ...$data,
            'personalization_template_id' => null,
            'is_active' => $request->boolean('is_active', true),
            'is_default' => false,
            'sort_order' => PersonalizationFont::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.personalization.fonts.index')
            ->with('status', 'Font added.');
    }

    public function update(Request $request, PersonalizationFont $font)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'internal_name'         => ['required', 'string', 'max:255'],
            'css_font_family'       => ['required', 'string', 'max:255'],
            'font_family'           => ['nullable', 'string', 'max:255'],
            'font_source_type'      => ['nullable', 'in:google,local,uploaded'],
            'font_source_value'     => ['nullable', 'string', 'max:2048'],
            'category'              => ['nullable', 'string', 'max:255'],
            'preview_label'         => ['nullable', 'string', 'max:255'],
            'preview_sample_text'   => ['nullable', 'string', 'max:255'],
            'font_weight_default'   => ['nullable', 'string', 'max:10'],
            'font_style_default'    => ['nullable', 'in:normal,italic'],
            'letter_spacing_default'=> ['nullable', 'numeric'],
            'line_height_default'   => ['nullable', 'numeric'],
            'text_transform_default'=> ['nullable', 'in:none,uppercase,lowercase,capitalize'],
            'recommended_for'       => ['nullable', 'string', 'max:255'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $font->update([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.personalization.fonts.index')
            ->with('status', 'Font updated.');
    }

    public function destroy(PersonalizationFont $font)
    {
        $font->delete();
        return redirect()->route('admin.personalization.fonts.index')
            ->with('status', 'Font deleted.');
    }
}
