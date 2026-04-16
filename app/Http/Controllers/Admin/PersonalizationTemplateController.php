<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PersonalizationTemplateRequest;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PersonalizationTemplateController extends Controller
{
    public function index()
    {
        $templates = PersonalizationTemplate::with('product')->latest()->paginate(12);

        return view('admin.personalization.templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new PersonalizationTemplate([
            'is_active' => true,
            'preview_rules' => ['safe_scale' => true, 'allow_multiline' => true],
            'render_rules' => ['export_format' => 'png', 'proof_required' => true],
        ]);

        return view('admin.personalization.templates.create', [
            'template' => $template,
            'products' => Product::where('type', ProductType::AdvancedPersonalized)->orderBy('name')->get(),
        ]);
    }

    public function store(PersonalizationTemplateRequest $request)
    {
        $template = DB::transaction(function () use ($request) {
            $productId = (int) $request->input('product_id');

            Product::whereKey($productId)->update(['type' => ProductType::AdvancedPersonalized]);

            $template = PersonalizationTemplate::create([
                'product_id' => $productId,
                'name' => $request->string('name')->toString(),
                'preview_image_url' => $request->string('preview_image_url')->toString(),
                'preview_rules' => [
                    'safe_scale' => $request->boolean('preview_rules.safe_scale'),
                    'allow_multiline' => $request->boolean('preview_rules.allow_multiline'),
                ],
                'render_rules' => [
                    'export_format' => $request->input('render_rules.export_format', 'png'),
                    'proof_required' => $request->boolean('render_rules.proof_required'),
                ],
                'instructions' => $request->input('instructions'),
                'proof_note_label' => $request->input('proof_note_label'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncTemplateChildren($template, $request->validated());

            return $template;
        });

        return redirect()->route('admin.personalization.templates.edit', $template)->with('status', 'Personalization template created.');
    }

    public function edit(PersonalizationTemplate $template)
    {
        $template->load(['product', 'fields', 'fonts']);

        return view('admin.personalization.templates.edit', [
            'template' => $template,
            'products' => Product::where('type', ProductType::AdvancedPersonalized)->orWhereKey($template->product_id)->orderBy('name')->get(),
        ]);
    }

    public function update(PersonalizationTemplateRequest $request, PersonalizationTemplate $template)
    {
        DB::transaction(function () use ($request, $template): void {
            $template->update([
                'product_id' => (int) $request->input('product_id'),
                'name' => $request->string('name')->toString(),
                'preview_image_url' => $request->string('preview_image_url')->toString(),
                'preview_rules' => [
                    'safe_scale' => $request->boolean('preview_rules.safe_scale'),
                    'allow_multiline' => $request->boolean('preview_rules.allow_multiline'),
                ],
                'render_rules' => [
                    'export_format' => $request->input('render_rules.export_format', 'png'),
                    'proof_required' => $request->boolean('render_rules.proof_required'),
                ],
                'instructions' => $request->input('instructions'),
                'proof_note_label' => $request->input('proof_note_label'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncTemplateChildren($template, $request->validated());
        });

        return redirect()->route('admin.personalization.templates.edit', $template)->with('status', 'Personalization template updated.');
    }

    private function syncTemplateChildren(PersonalizationTemplate $template, array $data): void
    {
        $template->fields()->delete();
        collect($data['fields'] ?? [])
            ->filter(fn (array $field) => filled($field['label'] ?? null) && filled($field['field_key'] ?? null))
            ->values()
            ->each(fn (array $field, int $index) => $template->fields()->create([
                'label' => $field['label'],
                'field_key' => $field['field_key'],
                'placeholder' => $field['placeholder'] ?? null,
                'help_text' => $field['help_text'] ?? null,
                'default_value' => $field['default_value'] ?? null,
                'max_length' => $field['max_length'] ?? 100,
                'min_length' => $field['min_length'] ?? 0,
                'font_size_min' => $field['font_size_min'] ?? 18,
                'font_size_max' => $field['font_size_max'] ?? 36,
                'line_height' => $field['line_height'] ?? 1.2,
                'letter_spacing' => $field['letter_spacing'] ?? 0,
                'text_align' => $field['text_align'] ?? 'center',
                'text_color' => $field['text_color'] ?? '#780000',
                'position_x' => $field['position_x'] ?? 50,
                'position_y' => $field['position_y'] ?? 50,
                'width' => $field['width'] ?? 70,
                'height' => $field['height'] ?? 10,
                'position' => $index,
            ]));

        $template->fonts()->delete();
        collect($data['fonts'] ?? [])
            ->filter(fn (array $font) => filled($font['name'] ?? null) && filled($font['css_font_family'] ?? null))
            ->values()
            ->each(fn (array $font, int $index) => $template->fonts()->create([
                'name' => $font['name'],
                'css_font_family' => $font['css_font_family'],
                'preview_label' => $font['preview_label'] ?? $font['name'],
                'position' => $index,
                'is_default' => (bool) ($font['is_default'] ?? false),
            ]));
    }
}
