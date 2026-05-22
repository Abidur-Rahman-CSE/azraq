<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PersonalizationTemplateRequest;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use App\Support\PersonalizationAssetUsage;
use App\Support\PersonalizationTemplateSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PersonalizationTemplateController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'status' => request('status'),
            'product_id' => request('product_id'),
        ];

        $templates = PersonalizationTemplate::query()
            ->with('product')
            ->withCount(['fields', 'fonts'])
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('product', fn (Builder $productQuery) => $productQuery->where('name', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when(filled($filters['status']), fn (Builder $query) => $query->where('is_active', $filters['status'] === 'active'))
            ->when(filled($filters['product_id']), fn (Builder $query) => $query->where('product_id', $filters['product_id']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $mockupsTableExists = Schema::hasTable('personalization_mockups');
        $mockupCounts = $mockupsTableExists
            ? DB::table('personalization_mockups')
                ->selectRaw('personalization_template_id, COUNT(*) as aggregate')
                ->groupBy('personalization_template_id')
                ->pluck('aggregate', 'personalization_template_id')
            : collect();

        $templates->getCollection()->transform(function (PersonalizationTemplate $template) use ($mockupCounts) {
            $template->setAttribute('mockups_count', (int) ($mockupCounts[$template->id] ?? 0));

            return $template;
        });

        $templatesWithMockups = $mockupsTableExists
            ? DB::table('personalization_mockups')->distinct()->count('personalization_template_id')
            : 0;

        return view('admin.personalization.templates.index', [
            'templates' => $templates,
            'filters' => $filters,
            'products' => Product::where('type', ProductType::AdvancedPersonalized)->orderBy('name')->get(),
            'stats' => [
                'missing_base_image' => PersonalizationTemplate::query()->where(function (Builder $query): void {
                    $query->whereNull('base_template_url')->orWhere('base_template_url', '');
                })->count(),
                'missing_fields' => PersonalizationTemplate::query()->doesntHave('fields')->count(),
                'missing_mockups' => $mockupsTableExists
                    ? max(PersonalizationTemplate::query()->count() - $templatesWithMockups, 0)
                    : PersonalizationTemplate::query()->count(),
            ],
        ]);
    }

    public function create()
    {
        $template = new PersonalizationTemplate([
            'is_active' => true,
            'export_ratio_width' => 9,
            'export_ratio_height' => 13,
            'preview_data_presets' => [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
                'ceremony_date' => '12 December 2026',
                'venue' => 'Dhaka',
            ],
            'preview_rules' => ['safe_scale' => true, 'allow_multiline' => true],
            'render_rules' => ['export_format' => 'png', 'proof_required' => true],
        ]);

        return view('admin.personalization.templates.create', $this->formData($template));
    }

    public function store(PersonalizationTemplateRequest $request)
    {
        $template = DB::transaction(function () use ($request) {
            $productId = $request->integer('product_id');

            if (filled($request->input('product_id'))) {
                Product::whereKey($productId)->update(['type' => ProductType::AdvancedPersonalized]);
            }

            $template = PersonalizationTemplate::create($this->templatePayload($request));

            $this->syncTemplateChildren($template, $request->validated());

            return $template;
        });

        PersonalizationTemplateSnapshot::regenerate($template);

        return redirect()
            ->route('admin.personalization.templates.edit', $template)
            ->with('status', $request->input('save_mode') === 'draft'
                ? 'Template draft saved without changing live status.'
                : 'Personalization template created.');
    }

    public function edit(PersonalizationTemplate $template)
    {
        $template->load(['product', 'fields', 'fonts']);

        return view('admin.personalization.templates.edit', $this->formData($template));
    }

    public function duplicate(PersonalizationTemplate $template)
    {
        $template->load(['fields', 'fonts']);

        $duplicate = DB::transaction(function () use ($template) {
            $copy = PersonalizationTemplate::create([
                ...collect($template->only([
                    'base_template_url',
                    'preview_image_url',
                    'mask_image_url',
                    'export_ratio_width',
                    'export_ratio_height',
                    'preview_rules',
                    'render_rules',
                    'preview_data_presets',
                    'instructions',
                    'safe_zone_notes',
                    'proof_note_label',
                ]))->all(),
                'product_id' => null,
                'name' => $template->name.' Copy',
                'thumbnail_image_url' => null,
                'is_active' => false,
            ]);

            foreach ($template->fields as $index => $field) {
                $copy->fields()->create([
                    ...collect($field->only([
                        'label',
                        'field_key',
                        'placeholder',
                        'help_text',
                        'default_value',
                        'is_required',
                        'max_length',
                        'min_length',
                        'font_size_min',
                        'font_size_max',
                        'line_height',
                        'letter_spacing',
                        'text_align',
                        'text_color',
                        'position_x',
                        'position_y',
                        'width',
                        'height',
                        'rotation',
                        'z_index',
                        'preview_sample_value',
                        'settings',
                    ]))->all(),
                    'position' => $index,
                ]);
            }

            foreach ($template->fonts as $index => $font) {
                $copy->fonts()->create([
                    ...collect($font->only([
                        'name',
                        'internal_name',
                        'css_font_family',
                        'preview_label',
                        'font_family',
                        'font_source_type',
                        'font_source_value',
                        'category',
                        'style_type',
                        'supported_use',
                        'preview_sample_text',
                        'font_weight_default',
                        'font_style_default',
                        'letter_spacing_default',
                        'line_height_default',
                        'text_transform_default',
                        'recommended_for',
                        'sort_order',
                        'is_default',
                        'is_active',
                    ]))->all(),
                    'position' => $index,
                ]);
            }

            return $copy;
        });

        PersonalizationTemplateSnapshot::regenerate($duplicate);

        return redirect()
            ->route('admin.personalization.templates.edit', $duplicate)
            ->with('status', 'Personalization template duplicated.');
    }

    public function update(PersonalizationTemplateRequest $request, PersonalizationTemplate $template)
    {
        DB::transaction(function () use ($request, $template): void {
            $template->update($this->templatePayload($request, $template));

            $this->syncTemplateChildren($template, $request->validated());
        });

        PersonalizationTemplateSnapshot::regenerate($template);

        return redirect()
            ->route('admin.personalization.templates.edit', $template)
            ->with('status', $request->input('save_mode') === 'draft'
                ? 'Template draft saved without changing live status.'
                : 'Personalization template updated.');
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
                'is_required' => (bool) ($field['is_required'] ?? false),
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
                'rotation' => $field['rotation'] ?? 0,
                'z_index' => $field['z_index'] ?? $index,
                'preview_sample_value' => $field['preview_sample_value'] ?? null,
                'settings' => [
                    'auto_fit' => (bool) data_get($field, 'settings.auto_fit', true),
                    'allow_multiline' => (bool) data_get($field, 'settings.allow_multiline', true),
                    'max_lines' => data_get($field, 'settings.max_lines', 3),
                    'overflow_behavior' => data_get($field, 'settings.overflow_behavior', 'shrink_then_wrap'),
                    'font_family_override' => data_get($field, 'settings.font_family_override'),
                    'font_weight' => data_get($field, 'settings.font_weight', '600'),
                    'text_transform' => data_get($field, 'settings.text_transform', 'none'),
                    'field_type' => data_get($field, 'settings.field_type', 'text'),
                    'date_format' => data_get($field, 'settings.date_format', 'long'),
                    'prefix'  => data_get($field, 'settings.prefix',  ''),
                    'postfix' => data_get($field, 'settings.postfix', ''),
                ],
                'position' => $index,
            ]));

        $template->fonts()->delete();
        collect($data['fonts'] ?? [])
            ->filter(fn (array $font) => filled($font['name'] ?? null) && filled($font['font_family'] ?? ($font['css_font_family'] ?? null)))
            ->sortBy(fn (array $font, int $index) => (int) ($font['sort_order'] ?? $index))
            ->values()
            ->each(fn (array $font, int $index) => $template->fonts()->create([
                'name' => $font['name'],
                'internal_name' => $font['internal_name'] ?? str($font['name'])->snake()->toString(),
                'css_font_family' => $font['css_font_family'] ?? $font['font_family'],
                'preview_label' => $font['preview_label'] ?? $font['name'],
                'font_family' => $font['font_family'] ?? $font['css_font_family'],
                'font_source_type' => $font['font_source_type'] ?? 'local',
                'font_source_value' => $font['font_source_value'] ?? null,
                'category' => $font['category'] ?? 'Minimal Sans',
                'style_type' => $font['style_type'] ?? ($font['category'] ?? 'Minimal Sans'),
                'supported_use' => $font['supported_use'] ?? 'all',
                'preview_sample_text' => $font['preview_sample_text'] ?? ($font['preview_label'] ?? $font['name']),
                'font_weight_default' => $font['font_weight_default'] ?? '600',
                'font_style_default' => $font['font_style_default'] ?? 'normal',
                'letter_spacing_default' => $font['letter_spacing_default'] ?? 0,
                'line_height_default' => $font['line_height_default'] ?? 1.2,
                'text_transform_default' => $font['text_transform_default'] ?? 'none',
                'recommended_for' => $font['recommended_for'] ?? 'all',
                'position' => $index,
                'sort_order' => (int) ($font['sort_order'] ?? $index),
                'is_default' => (bool) ($font['is_default'] ?? false),
                'is_active' => (bool) ($font['is_active'] ?? true),
            ]));
    }

    private function formData(PersonalizationTemplate $template): array
    {
        return [
            'template' => $template,
            'products' => Product::where('type', ProductType::AdvancedPersonalized)
                ->orWhere('id', $template->product_id)
                ->orderBy('name')
                ->get(),
        ];
    }

    private function templatePayload(PersonalizationTemplateRequest $request, ?PersonalizationTemplate $template = null): array
    {
        return [
            'product_id' => filled($request->input('product_id')) ? $request->integer('product_id') : null,
            'name' => $request->string('name')->toString(),
            'base_template_url' => $this->resolveUpload($request->file('base_template_upload'), $request->input('base_template_url'), $template?->base_template_url, $request->boolean('remove_base_template')),
            'preview_image_url' => $this->resolveUpload($request->file('preview_image_upload'), $request->input('preview_image_url'), $template?->preview_image_url, $request->boolean('remove_preview_image')),
            'mask_image_url' => $this->resolveUpload($request->file('mask_image_upload'), $request->input('mask_image_url'), $template?->mask_image_url, $request->boolean('remove_mask_image')),
            'export_ratio_width' => $request->integer('export_ratio_width') ?: 9,
            'export_ratio_height' => $request->integer('export_ratio_height') ?: 13,
            'preview_rules' => [
                'safe_scale' => $request->boolean('preview_rules.safe_scale'),
                'allow_multiline' => $request->boolean('preview_rules.allow_multiline'),
                'safe_editing' => $request->boolean('preview_rules.safe_editing', true),
                'default_min_font_size' => $request->integer('preview_rules.default_min_font_size') ?: 18,
                'default_max_font_size' => $request->integer('preview_rules.default_max_font_size') ?: 40,
                'default_line_height' => (float) $request->input('preview_rules.default_line_height', 1.2),
                'default_letter_spacing' => (float) $request->input('preview_rules.default_letter_spacing', 0),
                'auto_fit_enabled' => $request->boolean('preview_rules.auto_fit_enabled', true),
                'estimated_longest_safe_field' => $request->input('preview_rules.estimated_longest_safe_field'),
            ],
            'render_rules' => [
                'export_format' => $request->input('render_rules.export_format', 'png'),
                'proof_required' => $request->boolean('render_rules.proof_required'),
                'export_ratio_lock' => $request->boolean('render_rules.export_ratio_lock', true),
                'export_size' => $request->input('render_rules.export_size', 'Print-ready'),
            ],
            'preview_data_presets' => [
                'bride_name' => $request->input('preview_data_presets.bride_name'),
                'groom_name' => $request->input('preview_data_presets.groom_name'),
                'ceremony_date' => $request->input('preview_data_presets.ceremony_date'),
                'venue' => $request->input('preview_data_presets.venue'),
            ],
            'instructions' => $request->input('instructions'),
            'safe_zone_notes' => $request->input('safe_zone_notes'),
            'proof_note_label' => $request->input('proof_note_label'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function resolveUpload(?UploadedFile $file, ?string $inputUrl, ?string $currentUrl, bool $remove = false): ?string
    {
        if ($remove) {
            $this->deleteManagedAsset($currentUrl);

            return null;
        }

        if ($file instanceof UploadedFile) {
            $this->deleteManagedAsset($currentUrl);

            return Storage::url($file->store('personalization/templates', 'public'));
        }

        return $inputUrl ?: $currentUrl;
    }

    private function deleteManagedAsset(?string $url): void
    {
        PersonalizationAssetUsage::deleteManagedAssetIfUnused($url);
    }
}
