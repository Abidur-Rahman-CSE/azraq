@php
    $isEdit = $template->exists;
    $previewRules = array_merge([
        'safe_scale' => true,
        'allow_multiline' => true,
        'safe_editing' => true,
        'default_min_font_size' => 18,
        'default_max_font_size' => 40,
        'default_line_height' => 1.2,
        'default_letter_spacing' => 0,
        'auto_fit_enabled' => true,
        'estimated_longest_safe_field' => 'Bride and groom full names with venue',
    ], old('preview_rules', $template->preview_rules ?? []));

    $renderRules = array_merge([
        'export_format' => 'png',
        'proof_required' => true,
        'export_ratio_lock' => true,
        'export_size' => 'Print-ready',
    ], old('render_rules', $template->render_rules ?? []));

    $previewData = old('preview_data_presets', $template->preview_data_presets ?? [
        'bride_name' => 'Amena',
        'groom_name' => 'Hassan',
        'ceremony_date' => '12 December 2026',
        'venue' => 'Dhaka, Bangladesh',
    ]);

    $initialFields = collect(old('fields', $template->fields->map(fn ($field) => $field->toArray())->all()))
        ->values()
        ->map(function ($field, $index) use ($previewRules) {
            return [
                'id' => $field['id'] ?? ($index + 1),
                'label' => $field['label'] ?? '',
                'field_key' => $field['field_key'] ?? '',
                'placeholder' => $field['placeholder'] ?? '',
                'help_text' => $field['help_text'] ?? '',
                'default_value' => $field['default_value'] ?? '',
                'preview_sample_value' => $field['preview_sample_value'] ?? '',
                'is_required' => (bool) ($field['is_required'] ?? false),
                'max_length' => (int) ($field['max_length'] ?? 100),
                'min_length' => (int) ($field['min_length'] ?? 0),
                'font_size_min' => (int) ($field['font_size_min'] ?? $previewRules['default_min_font_size']),
                'font_size_max' => (int) ($field['font_size_max'] ?? $previewRules['default_max_font_size']),
                'line_height' => (float) ($field['line_height'] ?? $previewRules['default_line_height']),
                'letter_spacing' => (float) ($field['letter_spacing'] ?? $previewRules['default_letter_spacing']),
                'text_align' => $field['text_align'] ?? 'center',
                'text_color' => $field['text_color'] ?? '#780000',
                'position_x' => (float) ($field['position_x'] ?? 50),
                'position_y' => (float) ($field['position_y'] ?? 50),
                'width' => (float) ($field['width'] ?? 70),
                'height' => (float) ($field['height'] ?? 12),
                'rotation' => (float) ($field['rotation'] ?? 0),
                'z_index' => (int) ($field['z_index'] ?? $index),
                'settings' => [
                    'auto_fit' => (bool) data_get($field, 'settings.auto_fit', $previewRules['auto_fit_enabled']),
                    'allow_multiline' => (bool) data_get($field, 'settings.allow_multiline', $previewRules['allow_multiline']),
                    'max_lines' => (int) data_get($field, 'settings.max_lines', 3),
                    'overflow_behavior' => data_get($field, 'settings.overflow_behavior', 'shrink_then_wrap'),
                    'font_family_override' => data_get($field, 'settings.font_family_override', ''),
                    'font_weight' => (string) data_get($field, 'settings.font_weight', '600'),
                    'font_style'  => data_get($field, 'settings.font_style', 'normal'),
                    'text_transform' => data_get($field, 'settings.text_transform', 'none'),
                    'field_type' => data_get($field, 'settings.field_type', 'text'),
                    'date_format' => data_get($field, 'settings.date_format', 'long'),
                    'prefix'               => data_get($field, 'settings.prefix', ''),
                    'prefix_size'          => (float) data_get($field, 'settings.prefix_size', 0),
                    'prefix_weight_delta'  => (int)   data_get($field, 'settings.prefix_weight_delta', 0),
                    'prefix_italic_mode'   => data_get($field, 'settings.prefix_italic_mode', 'auto'),
                    'prefix_color'         => data_get($field, 'settings.prefix_color', ''),
                    'prefix_transform'     => data_get($field, 'settings.prefix_transform', 'none'),
                    'postfix'              => data_get($field, 'settings.postfix', ''),
                    'postfix_size'         => (float) data_get($field, 'settings.postfix_size', 0),
                    'postfix_weight_delta' => (int)   data_get($field, 'settings.postfix_weight_delta', 0),
                    'postfix_italic_mode'  => data_get($field, 'settings.postfix_italic_mode', 'auto'),
                    'postfix_color'        => data_get($field, 'settings.postfix_color', ''),
                    'postfix_transform'    => data_get($field, 'settings.postfix_transform', 'none'),
                ],
            ];
        })
        ->whenEmpty(fn ($collection) => $collection->push(
            [
                'id' => 1,
                'label' => 'Bride name',
                'field_key' => 'bride_name',
                'placeholder' => 'Bride name',
                'help_text' => 'Primary bride name line.',
                'default_value' => '',
                'preview_sample_value' => 'Amena',
                'is_required' => true,
                'max_length' => 100,
                'min_length' => 0,
                'font_size_min' => (int) $previewRules['default_min_font_size'],
                'font_size_max' => (int) $previewRules['default_max_font_size'],
                'line_height' => (float) $previewRules['default_line_height'],
                'letter_spacing' => (float) $previewRules['default_letter_spacing'],
                'text_align' => 'center',
                'text_color' => '#780000',
                'position_x' => 50,
                'position_y' => 28,
                'width' => 58,
                'height' => 14,
                'rotation' => 0,
                'z_index' => 0,
                'settings' => [
                    'auto_fit' => true,
                    'allow_multiline' => false,
                    'max_lines' => 1,
                    'overflow_behavior' => 'shrink_only',
                    'font_family_override' => '',
                    'font_weight' => '700',
                    'text_transform' => 'uppercase',
                ],
            ]
        ))
        ->all();

    $starterFontPresets = \App\Models\PersonalizationFont::starterPresets();

    $initialFonts = collect(old('fonts', $template->fonts->map(fn ($font) => $font->toArray())->all()))
        ->values()
        ->map(fn ($font, $index) => [
            'id' => $font['id'] ?? ($index + 1),
            'name' => $font['name'] ?? '',
            'internal_name' => $font['internal_name'] ?? str($font['name'] ?? 'preset_'.$index)->snake()->toString(),
            'css_font_family' => $font['css_font_family'] ?? '',
            'font_family' => $font['font_family'] ?? ($font['css_font_family'] ?? ''),
            'font_source_type' => $font['font_source_type'] ?? 'local',
            'font_source_value' => $font['font_source_value'] ?? null,
            'category' => $font['category'] ?? 'Minimal Sans',
            'style_type' => $font['style_type'] ?? ($font['category'] ?? 'Minimal Sans'),
            'supported_use' => $font['supported_use'] ?? 'all',
            'preview_label' => $font['preview_label'] ?? '',
            'preview_sample_text' => $font['preview_sample_text'] ?? ($font['preview_label'] ?? 'Amena & Hassan'),
            'font_weight_default' => (string) ($font['font_weight_default'] ?? '600'),
            'font_style_default' => $font['font_style_default'] ?? 'normal',
            'letter_spacing_default' => (float) ($font['letter_spacing_default'] ?? 0),
            'line_height_default' => (float) ($font['line_height_default'] ?? 1.2),
            'text_transform_default' => $font['text_transform_default'] ?? 'none',
            'recommended_for' => $font['recommended_for'] ?? 'all',
            'is_default' => (bool) ($font['is_default'] ?? false),
            'is_active' => (bool) ($font['is_active'] ?? true),
            'sort_order' => (int) ($font['sort_order'] ?? ($font['position'] ?? $index)),
        ])
        ->pipe(function ($collection) use ($starterFontPresets) {
            $existing = $collection->pluck('internal_name')->filter()->all();

            foreach ($starterFontPresets as $index => $preset) {
                if (in_array($preset['internal_name'], $existing, true)) {
                    continue;
                }

                $collection->push([
                    'id' => 'starter-'.$preset['internal_name'],
                    ...$preset,
                ]);
            }

            return $collection;
        })
        ->all();

    $fontStylesheetUrls = collect($initialFonts)
        ->filter(fn ($font) => ($font['font_source_type'] ?? 'local') === 'google' && filled($font['font_source_value'] ?? null))
        ->pluck('font_source_value')
        ->unique()
        ->values();
@endphp

@foreach ($fontStylesheetUrls as $fontStylesheetUrl)
    <link rel="stylesheet" href="{{ $fontStylesheetUrl }}">
@endforeach

<form
    id="template-editor-form"
    method="POST"
    action="{{ $isEdit ? route('admin.personalization.templates.update', $template) : route('admin.personalization.templates.store') }}"
    enctype="multipart/form-data"
    novalidate
    class="space-y-8"
    @submit="
        $el.querySelector('[name=fields_payload]').value = JSON.stringify(serializableFields());
        $el.querySelector('[name=fonts_payload]').value  = JSON.stringify(serializableFonts());
    "
    x-data="nikahTemplateEditor({
        mode: @js($isEdit ? 'edit' : 'create'),
        templateName: @js(old('name', $template->name)),
        assignedProductId: @js((string) old('product_id', $template->product_id)),
        instructions: @js(old('instructions', $template->instructions)),
        proofNoteLabel: @js(old('proof_note_label', $template->proof_note_label)),
        safeZoneNotes: @js(old('safe_zone_notes', $template->safe_zone_notes)),
        isTemplateActive: @js((bool) old('is_active', $template->is_active)),
        baseTemplateUrl: @js(old('base_template_url', $template->base_template_url)),
        previewImageUrl: @js(old('preview_image_url', $template->preview_image_url)),
        maskImageUrl: @js(old('mask_image_url', $template->mask_image_url)),
        removeBaseTemplate: @js((bool) old('remove_base_template', false)),
        removePreviewImage: @js((bool) old('remove_preview_image', false)),
        removeMaskImage: @js((bool) old('remove_mask_image', false)),
        exportRatioWidth: @js((int) old('export_ratio_width', $template->export_ratio_width ?? 9)),
        exportRatioHeight: @js((int) old('export_ratio_height', $template->export_ratio_height ?? 13)),
        previewRules: @js($previewRules),
        renderRules: @js($renderRules),
        previewData: @js($previewData),
        initialFields: @js($initialFields),
        initialFonts: @js($initialFonts),
    })"
    x-init="init()"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="is_active" :value="isTemplateActive ? 1 : 0">

    @if ($errors->any())
        <div class="surface-card border border-[rgba(193,18,31,0.18)] bg-[rgba(253,240,213,0.75)] p-5">
            <p class="text-sm font-semibold text-[var(--color-primary-900)]">Please review the highlighted fields before saving.</p>
            <ul class="mt-3 space-y-1 text-sm text-[var(--color-text-soft)]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Flat Certificate Editor</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Edit Nikah Nama template</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">
                    Define certificate artwork, safe areas, text zones, and fitting behavior.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="button-ghost" name="save_mode" value="draft">Save draft</button>
                <button type="submit" class="button-primary" name="save_mode" value="template">Save template</button>
                <a href="{{ route('admin.personalization.templates.index') }}" class="button-ghost">Back to templates</a>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-12">
            <div class="rounded-[30px] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5 shadow-[0_16px_40px_rgba(0,48,73,0.04)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Quick actions</p>
                <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1.2fr)_repeat(4,minmax(0,0.8fr))]">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Add field from preset</span>
                        <select class="field-select" x-model="selectedPreset">
                            <option value="">Select preset</option>
                            <template x-for="preset in fieldPresets" :key="preset.key">
                                <option :value="preset.key" x-text="preset.label"></option>
                            </template>
                        </select>
                    </label>
                    <button type="button" class="button-ghost w-full justify-center" @click="selectedPreset ? addField(selectedPreset) : addField()">Add new field</button>
                    <button type="button" class="button-ghost w-full justify-center" @click="activeFieldId && duplicateField(activeFieldId)" :disabled="! activeFieldId">Duplicate selected field</button>
                    <button type="button" class="button-ghost w-full justify-center" @click="activeFieldId && confirmDeleteField(activeFieldId)" :disabled="! activeFieldId">Delete selected field</button>
                    <button type="submit" class="button-primary w-full justify-center" name="save_mode" value="template">Save template</button>
                </div>
            </div>
        </div>

        <aside class="space-y-5 xl:col-span-12 xl:grid xl:grid-cols-12 xl:gap-5 xl:space-y-0">
            <div class="rounded-[30px] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5 shadow-[0_16px_40px_rgba(0,48,73,0.04)] xl:col-span-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Template identity</p>
                <div class="mt-4 grid gap-4">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Assigned product</span>
                        <select name="product_id" class="field-select" x-model="assignedProductId">
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Template name</span>
                        <input type="text" name="name" class="field-input" x-model="templateName">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Instructions</span>
                        <textarea name="instructions" rows="4" class="field-textarea" x-model="instructions"></textarea>
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Proof note label</span>
                        <input type="text" name="proof_note_label" class="field-input" x-model="proofNoteLabel">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Safe-zone notes</span>
                        <textarea name="safe_zone_notes" rows="3" class="field-textarea" x-model="safeZoneNotes"></textarea>
                    </label>

                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-[rgba(255,255,255,0.9)] px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Active template</span>
                        <input type="checkbox" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="isTemplateActive">
                    </label>
                </div>
            </div>

            <div class="rounded-[30px] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5 shadow-[0_16px_40px_rgba(0,48,73,0.04)] xl:col-span-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Artwork assets</p>
                        <h3 class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">Base template, preview image, and mask</h3>
                    </div>
                    <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Base required</span>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="rounded-[24px] border border-[rgba(120,0,0,0.12)] bg-[rgba(253,240,213,0.42)] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Base template image</p>
                                <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Main editable Nikah Nama artwork</p>
                            </div>
                            <button type="button" class="button-ghost !px-3 !py-2" x-show="assetValue('baseTemplateUrl')" @click="clearAsset('baseTemplateUrl')">Remove</button>
                        </div>
                        <input type="hidden" name="remove_base_template" :value="removeBaseTemplate ? 1 : 0">

                        <template x-if="assetValue('baseTemplateUrl')">
                            <div class="mt-4 overflow-hidden rounded-[22px] border border-[var(--color-border-soft)] bg-white">
                                <img :src="assetValue('baseTemplateUrl')" alt="Base template image" class="aspect-[4/3] w-full object-cover">
                            </div>
                        </template>

                        <div class="mt-4 grid gap-3">
                            <label class="field-shell">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload base artwork</span>
                                <input type="file" name="base_template_upload" accept="image/*" class="field-input" @change="selectAsset('base'); swapAssetPreview('baseTemplateUrl', $event)">
                            </label>
                            @error('base_template_upload')
                                <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                            @enderror

                            <label class="field-shell">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Or set asset path / URL</span>
                                <input type="text" name="base_template_url" class="field-input" x-model="baseTemplateUrl" @focus="selectAsset('base')" @input="syncAssetUrl('baseTemplateUrl', $event.target.value)">
                            </label>
                            @error('base_template_url')
                                <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-[var(--color-border-soft)] bg-white/80">
                        <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left" @click="showAdvancedAssets = !showAdvancedAssets">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Advanced assets and rendering</p>
                                <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Optional preview image, optional mask image, and future render notes</p>
                            </div>
                            <span class="text-sm font-semibold text-[var(--color-primary-900)]" x-text="showAdvancedAssets ? 'Hide' : 'Show'"></span>
                        </button>

                        <div x-cloak x-show="showAdvancedAssets" x-transition.opacity.duration.150ms class="border-t border-[var(--color-border-soft)] px-4 py-4">
                            <div class="space-y-4">
                                <template x-for="asset in advancedAssets()" :key="asset.key">
                                    <div class="rounded-[22px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="asset.title"></p>
                                                <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]" x-text="asset.help"></p>
                                            </div>
                                            <button type="button" class="button-ghost !px-3 !py-2" x-show="asset.value" @click="clearAsset(asset.stateKey)">Remove</button>
                                        </div>
                                        <input type="hidden" :name="asset.removeName" :value="asset.removeFlag ? 1 : 0">

                                        <template x-if="selectedAsset === asset.key && asset.value">
                                            <div class="mt-4 overflow-hidden rounded-[20px] border border-[var(--color-border-soft)] bg-white">
                                                <img :src="asset.value" :alt="asset.title" class="aspect-[4/3] w-full object-cover">
                                            </div>
                                        </template>

                                        <div class="mt-4 grid gap-3">
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload asset</span>
                                                <input type="file" :name="asset.uploadName" accept="image/*" class="field-input" @focus="selectAsset(asset.key)" @change="selectAsset(asset.key); swapAssetPreview(asset.stateKey, $event)">
                                            </label>
                                            <template x-if="asset.key === 'preview'">
                                                <div>
                                                    @error('preview_image_upload')
                                                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </template>
                                            <template x-if="asset.key === 'mask'">
                                                <div>
                                                    @error('mask_image_upload')
                                                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </template>
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Asset path / URL</span>
                                                <input type="text" class="field-input" :name="asset.urlName" :value="asset.value" @focus="selectAsset(asset.key)" @input="syncAssetUrl(asset.stateKey, $event.target.value)">
                                            </label>
                                            <template x-if="asset.key === 'preview'">
                                                <div>
                                                    @error('preview_image_url')
                                                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </template>
                                            <template x-if="asset.key === 'mask'">
                                                <div>
                                                    @error('mask_image_url')
                                                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div class="rounded-[22px] border border-dashed border-[var(--color-border-soft)] bg-white/70 px-4 py-3 text-xs leading-6 text-[var(--color-text-soft)]">
                                    Use preview image only when you need a polished display version. Use mask image only for advanced rendering and clipping in downstream mockup work.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[30px] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5 shadow-[0_16px_40px_rgba(0,48,73,0.04)] xl:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Proof and export</p>
                <div class="mt-4 grid gap-4">
                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Proof required</span>
                        <input type="hidden" name="render_rules[proof_required]" value="0">
                        <input type="checkbox" name="render_rules[proof_required]" value="1" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="renderRules.proof_required">
                    </label>
                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Safe editing</span>
                        <input type="hidden" name="preview_rules[safe_editing]" value="0">
                        <input type="checkbox" name="preview_rules[safe_editing]" value="1" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="previewRules.safe_editing">
                    </label>
                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Allow multiline</span>
                        <input type="hidden" name="preview_rules[allow_multiline]" value="0">
                        <input type="checkbox" name="preview_rules[allow_multiline]" value="1" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="previewRules.allow_multiline">
                    </label>
                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Export ratio lock</span>
                        <input type="hidden" name="render_rules[export_ratio_lock]" value="0">
                        <input type="checkbox" name="render_rules[export_ratio_lock]" value="1" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="renderRules.export_ratio_lock">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Export format</span>
                        <select name="render_rules[export_format]" class="field-select" x-model="renderRules.export_format">
                            <option value="png">PNG</option>
                            <option value="svg">SVG</option>
                            <option value="pdf">PDF-ready</option>
                        </select>
                    </label>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Export ratio width</span>
                            <input type="number" min="1" max="100" name="export_ratio_width" class="field-input" x-model.number="exportRatioWidth">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Export ratio height</span>
                            <input type="number" min="1" max="100" name="export_ratio_height" class="field-input" x-model.number="exportRatioHeight">
                        </label>
                    </div>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Export size</span>
                        <select name="render_rules[export_size]" class="field-select" x-model="renderRules.export_size">
                            <option value="Proof">Proof</option>
                            <option value="Print-ready">Print-ready</option>
                            <option value="Large-format">Large-format</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="rounded-[30px] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5 shadow-[0_16px_40px_rgba(0,48,73,0.04)] xl:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Global fit rules</p>
                <div class="mt-4 grid gap-4">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default min font size</span>
                        <input type="number" min="8" max="200" name="preview_rules[default_min_font_size]" class="field-input" x-model.number="previewRules.default_min_font_size">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default max font size</span>
                        <input type="number" min="8" max="240" name="preview_rules[default_max_font_size]" class="field-input" x-model.number="previewRules.default_max_font_size">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default line height</span>
                        <input type="number" step="0.01" min="0.5" max="3" name="preview_rules[default_line_height]" class="field-input" x-model.number="previewRules.default_line_height">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default letter spacing</span>
                        <input type="number" step="0.01" min="-10" max="30" name="preview_rules[default_letter_spacing]" class="field-input" x-model.number="previewRules.default_letter_spacing">
                    </label>
                    <label class="inline-flex items-center justify-between gap-4 rounded-[24px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <span>Auto-fit enabled by default</span>
                        <input type="hidden" name="preview_rules[auto_fit_enabled]" value="0">
                        <input type="checkbox" name="preview_rules[auto_fit_enabled]" value="1" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="previewRules.auto_fit_enabled">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Estimated longest safe field helper</span>
                        <input type="text" name="preview_rules[estimated_longest_safe_field]" class="field-input" x-model="previewRules.estimated_longest_safe_field">
                    </label>
                </div>
            </div>

        </aside>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.38fr)_minmax(22rem,0.92fr)]" @pointermove.window="pointerMove($event)" @pointerup.window="pointerUp()">
        <div class="surface-card p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Live certificate canvas</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Live certificate canvas</h3>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Click a field zone to edit it. Drag directly on the canvas to reposition.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="button-ghost" @click="canvasZoom = Math.max(0.8, Number((canvasZoom - 0.1).toFixed(2)))">Zoom out</button>
                    <button type="button" class="button-ghost" @click="canvasZoom = Math.min(1.6, Number((canvasZoom + 0.1).toFixed(2)))">Zoom in</button>
                    <button type="button" class="button-ghost" @click="resetView()">Reset view</button>
                    <button type="button" class="button-ghost" @click="showSafeAreas = !showSafeAreas" x-text="showSafeAreas ? 'Hide safe areas' : 'Show safe areas'"></button>
                    <button type="button" class="button-ghost" @click="showFieldBounds = !showFieldBounds" x-text="showFieldBounds ? 'Hide outlines' : 'Show outlines'"></button>
                </div>
            </div>

            <div class="mt-6 rounded-[32px] border border-[rgba(120,0,0,0.1)] bg-[linear-gradient(180deg,rgba(253,240,213,0.78),rgba(255,255,255,0.96))] p-4 sm:p-6">
                <div class="mx-auto max-w-4xl">
                    <div class="overflow-auto rounded-[28px] border border-[rgba(0,48,73,0.08)] bg-white/60 p-4 sm:p-6">
                        <div
                            x-ref="previewStage"
                            tabindex="0"
                            class="relative mx-auto overflow-hidden rounded-[28px] border border-[rgba(120,0,0,0.12)] bg-white shadow-[0_32px_80px_rgba(0,48,73,0.12)] outline-none"
                            :style="stageStyle()"
                            @click.self="clearSelection()"
                            @keydown.stop.prevent="handleCanvasKeydown($event)"
                        >
                            <div class="absolute inset-0 transition-transform duration-200 ease-out" :style="`transform: scale(${canvasZoom}); transform-origin: center top;`">
                                <template x-if="canvasArtworkUrl">
                                    <img :src="canvasArtworkUrl" alt="Template artwork" class="absolute inset-0 h-full w-full object-cover">
                                </template>
                                <template x-if="!canvasArtworkUrl">
                                    <div class="flex h-full items-center justify-center bg-[linear-gradient(135deg,rgba(253,240,213,0.78),rgba(255,255,255,0.96))] px-10 text-center text-sm leading-7 text-[var(--color-text-soft)]">
                                        Upload the base template image to start placing text zones on the Nikah Nama artwork.
                                    </div>
                                </template>

                                <div class="absolute inset-0 bg-white/20"></div>

                                <template x-if="showSafeAreas">
                                    <div class="absolute inset-[9%] rounded-[24px] border border-dashed border-[rgba(0,48,73,0.18)] bg-[rgba(255,255,255,0.18)]">
                                        <div class="absolute left-4 top-4 rounded-full bg-white/88 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--color-secondary-900)]">Safe area</div>
                                    </div>
                                </template>

                                <template x-for="field in sortedFields()" :key="field.id">
                                    <div
                                        class="absolute transition-[box-shadow,transform] duration-150"
                                        :style="canvasFieldShellStyle(field)"
                                        @click.stop="focusField(field.id, { scroll: false })"
                                        @mousedown.prevent="beginDragById(field.id, $event)"
                                    >
                                        <div
                                            class="flex h-full w-full items-center justify-center overflow-hidden rounded-[22px] px-[6px] text-center"
                                            :class="canvasFieldClass(field)"
                                            :style="canvasFieldInnerStyle(field)"
                                        >
                                            <p class="max-w-full break-words leading-tight" :style="canvasFieldTextStyle(field)" x-text="fieldPreviewText(field)"></p>
                                        </div>


                                        <template x-if="activeFieldId === field.id">
                                            <div>
                                                <div class="absolute -top-7 left-1/2 -translate-x-1/2 rounded-full bg-[rgba(255,255,255,0.94)] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)] shadow-[0_10px_24px_rgba(0,48,73,0.12)]" x-text="field.label || field.field_key || 'Field'"></div>
                                                <button type="button" class="absolute -left-2 -top-2 h-4 w-4 rounded-full border-2 border-white bg-[var(--color-primary-900)] shadow-[0_8px_18px_rgba(0,48,73,0.2)]" @mousedown.prevent.stop="beginResizeById(field.id, 'top-left', $event)"></button>
                                                <button type="button" class="absolute -right-2 -top-2 h-4 w-4 rounded-full border-2 border-white bg-[var(--color-primary-900)] shadow-[0_8px_18px_rgba(0,48,73,0.2)]" @mousedown.prevent.stop="beginResizeById(field.id, 'top-right', $event)"></button>
                                                <button type="button" class="absolute -bottom-2 -right-2 h-4 w-4 rounded-full border-2 border-white bg-[var(--color-primary-900)] shadow-[0_8px_18px_rgba(0,48,73,0.2)]" @mousedown.prevent.stop="beginResizeById(field.id, 'bottom-right', $event)"></button>
                                                <button type="button" class="absolute -bottom-2 -left-2 h-4 w-4 rounded-full border-2 border-white bg-[var(--color-primary-900)] shadow-[0_8px_18px_rgba(0,48,73,0.2)]" @mousedown.prevent.stop="beginResizeById(field.id, 'bottom-left', $event)"></button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-[26px] border border-[rgba(0,48,73,0.08)] bg-[rgba(255,255,255,0.9)] px-4 py-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="activeFieldSummary()"></p>
                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]" x-text="activeFieldMessage()"></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="button-ghost !px-3 !py-2" @click="selectedPreset ? addField(selectedPreset) : addField()">Add field</button>
                        <button type="button" class="button-ghost !px-3 !py-2" @click="focusAccordionById(activeFieldId)" x-show="activeFieldId">Open field controls</button>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs leading-6 text-[var(--color-text-soft)]">
                    <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-3 py-1">Drag to move</span>
                    <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-3 py-1">Arrow keys to nudge</span>
                    <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-3 py-1">Shift for larger movement</span>
                    <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-3 py-1">Alt for micro movement</span>
                </div>
            </div>
        </div>

        <aside class="surface-card p-4 sm:p-5 xl:sticky xl:top-24 xl:self-start" @click.outside="clearSelection()">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Text fields and safe areas</p>
                    <h3 class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]">Field editor</h3>
                    <p class="mt-1.5 text-xs leading-6 text-[var(--color-text-soft)]">One field open at a time for faster editing.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="field-shell min-w-[200px]">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Preset</span>
                        <select class="field-select" x-model="selectedPreset">
                            <option value="">Select preset</option>
                            <template x-for="preset in fieldPresets" :key="preset.key">
                                <option :value="preset.key" x-text="preset.label"></option>
                            </template>
                        </select>
                    </label>
                    <button type="button" class="button-ghost !px-3 !py-2 text-sm" @click="selectedPreset ? addField(selectedPreset) : addField()">Add field</button>
                </div>
            </div>

            <div class="mt-3 rounded-[20px] border border-[rgba(0,48,73,0.08)] bg-[rgba(253,240,213,0.38)] p-2.5"
                 x-data="{ open: false }">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <button type="button" class="flex items-center gap-1.5 text-left" @click="open = !open">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 text-[var(--color-text-soft)] transition-transform duration-150"
                             :class="open ? 'rotate-90' : ''"
                             viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 2l4 4-4 4"/>
                        </svg>
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Collective field controls</p>
                        <span x-show="!open" class="text-[10px] text-[var(--color-text-soft)]">click to expand</span>
                    </button>
                    <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyGlobalTypeDefaults()">Use defaults</button>
                </div>

                <div class="mt-2 grid gap-2 xl:grid-cols-2" x-show="open" x-transition.duration.150ms>
                    <div class="rounded-[16px] border border-[var(--color-border-soft)] bg-white/90 p-2.5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Layout and color</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-medium text-[var(--color-secondary-900)]">Align all</span>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyAllAlignment('start')">Left</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyAllAlignment('center')">Center</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyAllAlignment('end')">Right</button>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <label class="field-shell min-w-[170px] flex-1">
                                <span class="text-xs font-medium text-[var(--color-secondary-900)]">Text color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" class="h-9 w-11 cursor-pointer rounded-[12px] border border-[var(--color-border-soft)] bg-white p-1" x-model="collectiveTextColor">
                                    <input type="text" class="field-input" x-model="collectiveTextColor">
                                </div>
                            </label>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyAllTextColor()">Apply color</button>
                        </div>
                    </div>

                    <div class="rounded-[16px] border border-[var(--color-border-soft)] bg-white/90 p-2.5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Typography scale</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="adjustAllFontSizes(-2)">Font -</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="adjustAllFontSizes(2)">Font +</button>
                            <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-2 py-1 text-[10px] font-medium text-[var(--color-text-soft)]">Affects min and max for all</span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-end gap-2">
                            <label class="field-shell min-w-[200px] flex-1">
                                <span class="text-xs font-medium text-[var(--color-secondary-900)]">Font weight</span>
                                <select class="field-select" x-model="collectiveFontWeight">
                                    <option value="400">Regular</option>
                                    <option value="500">Medium</option>
                                    <option value="600">Semibold</option>
                                    <option value="700">Bold</option>
                                    <option value="800">Extra bold</option>
                                </select>
                            </label>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="applyAllFontWeight()">Apply weight</button>
                        </div>
                    </div>

                    <div class="rounded-[16px] border border-[var(--color-border-soft)] bg-white/90 p-2.5 xl:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Fitting behavior</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="setAllAutoFit(true)">Auto-fit all ON</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="setAllAutoFit(false)">Auto-fit all OFF</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="setAllMultiline(true)">Multiline all ON</button>
                            <button type="button" class="button-ghost !px-2.5 !py-1.5 text-xs" @click="setAllMultiline(false)">Multiline all OFF</button>
                            <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-2 py-1 text-[10px] font-medium text-[var(--color-text-soft)]">Useful for normalizing all zones quickly</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 space-y-2.5 max-h-[72vh] overflow-y-auto pr-1">
            <template x-for="(field, index) in fields" :key="field.id">
                <div :id="`field-accordion-${field.id}`"
                     class="rounded-[20px] border bg-white/90 shadow-[0_10px_24px_rgba(0,48,73,0.05)] transition-[border-color,box-shadow] duration-150"
                     :class="activeFieldId === field.id
                         ? 'border-[var(--color-primary-900)] shadow-[0_0_0_2px_rgba(120,0,0,0.10),0_10px_24px_rgba(0,48,73,0.08)]'
                         : 'border-[rgba(0,48,73,0.08)]'">
                    <div class="px-3 py-2">
                        {{-- Row 1: label + icon actions --}}
                        <div class="flex items-start justify-between gap-2">
                            <button type="button" class="min-w-0 flex-1 text-left" @click="toggleField(index)">
                                <p class="text-sm font-semibold leading-tight text-[var(--color-secondary-900)]" x-text="field.label || 'Untitled field'"></p>
                            </button>
                            <div class="flex flex-shrink-0 items-center gap-0.5">
                                <button type="button" class="field-icon-btn" @click.stop="toggleField(index)" :title="openFieldIndex === index ? 'Collapse' : 'Expand'">
                                    <template x-if="openFieldIndex !== index"><svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5l4 4 4-4"/></svg></template>
                                    <template x-if="openFieldIndex === index"><svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l4-4 4 4"/></svg></template>
                                </button>
                                <button type="button" class="field-icon-btn" @click.stop="focusField(field.id)" title="Focus on canvas">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="10" height="10" rx="1.5"/><path d="M5 5h4v4H5z"/></svg>
                                </button>
                                <button type="button" class="field-icon-btn" @click.stop="moveField(field.id,-1)" title="Move up">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 11V3M4 6l3-3 3 3"/></svg>
                                </button>
                                <button type="button" class="field-icon-btn" @click.stop="moveField(field.id,1)" title="Move down">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v8M4 8l3 3 3-3"/></svg>
                                </button>
                                <button type="button" class="field-icon-btn" @click.stop="duplicateField(field.id)" title="Duplicate">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="8" height="9" rx="1.2"/><path d="M5 4V2.2A1.5 1.5 0 016.5 1H11a1.5 1.5 0 011.5 1.5V9"/></svg>
                                </button>
                                <button type="button" class="field-icon-btn !text-[rgba(193,18,31,0.55)] hover:!text-[var(--color-primary-900)] hover:!bg-[rgba(193,18,31,0.07)]" @click.stop="confirmDeleteField(field.id)" title="Delete">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 4h10M5 4V2.5h4V4M5.5 7v3.5M8.5 7v3.5M3.5 4l.5 7.5h6l.5-7.5"/></svg>
                                </button>
                            </div>
                        </div>
                        {{-- Row 2: all badges on one line --}}
                        <div class="mt-1 flex flex-wrap items-center gap-1">
                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.12em] text-[var(--color-secondary-900)]" x-text="field.field_key || 'field_key'"></span>
                            <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.12em]"
                                  :class="{
                                    'bg-[rgba(120,0,0,0.10)] text-[var(--color-primary-900)]': field.field_type === 'date',
                                    'bg-[rgba(0,48,73,0.10)] text-[var(--color-secondary-900)]': field.field_type === 'static',
                                    'bg-[rgba(102,155,188,0.16)] text-[var(--color-secondary-900)]': !field.field_type || field.field_type === 'text',
                                  }"
                                  x-text="field.field_type || 'text'"></span>
                            <span x-show="field.field_type !== 'static'" class="rounded-full px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.12em]" :class="fitBadgeClass(field)" x-text="fitBadgeLabel(field)"></span>
                            <span class="rounded-full bg-[rgba(253,240,213,0.95)] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.12em] text-[var(--color-primary-900)]" x-text="`${field.font_size_min}–${field.font_size_max}px`"></span>
                            <template x-if="activeFieldId === field.id">
                                <span class="rounded-full bg-[rgba(193,18,31,0.08)] px-2 py-0.5 text-[9px] font-semibold text-[var(--color-primary-900)]">● canvas</span>
                            </template>
                        </div>
                        {{-- Row 3: preview text + fit detail --}}
                        <div class="mt-0.5 flex items-center gap-2">
                            <p class="min-w-0 flex-1 truncate text-[11px] leading-4 text-[var(--color-text-soft)]" x-text="fieldPreviewText(field)"></p>
                            <span class="flex-shrink-0 text-[10px] text-[var(--color-text-soft)]" x-text="fitBadgeDetail(field)"></span>
                        </div>
                    </div>

                    <div x-cloak x-show="openFieldIndex === index" x-transition.opacity.duration.150ms class="border-t border-[var(--color-border-soft)] px-3.5 py-3.5">
                        <div class="flex flex-wrap gap-1.5 border-b border-[var(--color-border-soft)] pb-2.5">
                            <template x-for="tab in fieldTabs" :key="tab.key">
                                <button type="button" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" :class="currentFieldTab(index) === tab.key ? 'bg-[var(--color-primary-900)] text-white' : 'bg-[rgba(0,48,73,0.08)] text-[var(--color-secondary-900)]'" @click="setFieldTab(index, tab.key)" x-text="tab.label"></button>
                            </template>
                        </div>

                        <div class="mt-3.5">
                            <div x-show="currentFieldTab(index) === 'basic'" class="space-y-3">
                                {{-- Type selector --}}
                                <div class="field-shell">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Field type</span>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="t in [{k:'text',lbl:'Text input'},{k:'date',lbl:'Date picker'},{k:'static',lbl:'Static (non-editable)'}]" :key="t.k">
                                            <label class="cursor-pointer">
                                                <input type="radio" :value="t.k" x-model="field.field_type" class="sr-only"
                                                       @change="field.settings.field_type = field.field_type">
                                                <span class="block rounded-full border px-3 py-1.5 text-[0.72rem] font-semibold transition"
                                                      :class="field.field_type === t.k ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.06)] text-[var(--color-primary-900)]' : 'border-[var(--color-border-soft)] text-[var(--color-text-soft)] hover:border-[var(--color-primary-900)]'"
                                                      x-text="t.lbl"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="field-shell">
                                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Label</span>
                                        <input class="field-input" x-model="field.label">
                                    </label>
                                    <label class="field-shell">
                                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Field key</span>
                                        <input class="field-input" x-model="field.field_key">
                                    </label>

                                    {{-- Static: content is default_value --}}
                                    <template x-if="field.field_type === 'static'">
                                        <label class="field-shell md:col-span-2">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Static text content</span>
                                            <textarea class="field-input" rows="2" x-model="field.default_value"></textarea>
                                            <span class="text-[11px] text-[var(--color-text-soft)]">This text is shown on the certificate. Not editable by the customer.</span>
                                        </label>
                                    </template>

                                    {{-- Text/Date: normal fields --}}
                                    <template x-if="field.field_type !== 'static'">
                                        <div class="contents">
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Placeholder</span>
                                                <input class="field-input" x-model="field.placeholder">
                                            </label>
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default value</span>
                                                <input class="field-input" x-model="field.default_value">
                                            </label>
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Prefix <span class="font-normal text-[var(--color-text-soft)]">(non-editable before input)</span></span>
                                                <input class="field-input" x-model="field.settings.prefix" placeholder="e.g. THIS AGREEMENT MADE ON THE">
                                            </label>
                                            <label class="field-shell">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Postfix <span class="font-normal text-[var(--color-text-soft)]">(non-editable after input)</span></span>
                                                <input class="field-input" x-model="field.settings.postfix" placeholder="e.g. AH">
                                            </label>
                                            <label class="field-shell md:col-span-2">
                                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Help text</span>
                                                <input class="field-input" x-model="field.help_text">
                                            </label>
                                            <label class="inline-flex items-center justify-between gap-4 rounded-[22px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                                <span>Required</span>
                                                <input type="checkbox" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="field.is_required">
                                            </label>
                                        </div>
                                    </template>
                                </div>

                                {{-- Font size: compact dual range --}}
                                <div class="field-shell">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Font size range <span class="font-normal text-[var(--color-text-soft)]">(min → max px)</span></span>
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="6" max="200" class="field-input w-20 text-center" x-model.number="field.font_size_min" @input="syncFontBounds(field,'min')">
                                        <span class="text-[var(--color-text-soft)]">→</span>
                                        <input type="number" min="6" max="200" class="field-input w-20 text-center" x-model.number="field.font_size_max" @input="syncFontBounds(field,'max')">
                                        <span class="text-xs text-[var(--color-text-soft)]">px on screen canvas
                                            <span class="block" x-text="`≈ ${Math.round(field.font_size_min * (2480/980) / (300/72))}–${Math.round(field.font_size_max * (2480/980) / (300/72))} pt on A4`"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="currentFieldTab(index) === 'layout'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">X</span><input type="number" step="0.01" class="field-input" x-model.number="field.position_x"></label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Y</span><input type="number" step="0.01" class="field-input" x-model.number="field.position_y"></label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Width</span><input type="number" step="0.01" class="field-input" x-model.number="field.width"></label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Height</span><input type="number" step="0.01" class="field-input" x-model.number="field.height"></label>
                                <label class="field-shell">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Alignment</span>
                                    <select class="field-select" x-model="field.text_align">
                                        <option value="start">Left</option>
                                        <option value="center">Center</option>
                                        <option value="end">Right</option>
                                    </select>
                                </label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Rotation</span><input type="number" step="0.01" class="field-input" x-model.number="field.rotation"></label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Z-index</span><input type="number" min="0" class="field-input" x-model.number="field.z_index"></label>
                            </div>

                            <div x-show="currentFieldTab(index) === 'typography'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Color</span><input class="field-input" x-model="field.text_color"></label>
                                <div class="field-shell md:col-span-2 xl:col-span-3">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Font family <span class="font-normal text-[var(--color-text-soft)]">(blank = customer's chosen preset)</span></span>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <label class="cursor-pointer">
                                            <input type="radio" value="" x-model="field.settings.font_family_override" class="sr-only">
                                            <span class="block rounded-full border px-3 py-1.5 text-[0.72rem] font-semibold transition"
                                                  :class="!field.settings.font_family_override ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.06)] text-[var(--color-primary-900)]' : 'border-[var(--color-border-soft)] text-[var(--color-text-soft)]'">
                                                Auto
                                            </span>
                                        </label>
                                        <template x-for="f in sortedFonts()" :key="f.id">
                                            <label class="cursor-pointer">
                                                <input type="radio" :value="f.font_family || f.css_font_family" x-model="field.settings.font_family_override" class="sr-only">
                                                <span class="block rounded-full border px-3 py-1.5 text-sm transition"
                                                      :class="field.settings.font_family_override === (f.font_family || f.css_font_family) ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.06)] text-[var(--color-primary-900)]' : 'border-[var(--color-border-soft)] text-[var(--color-text-soft)]'"
                                                      :style="`font-family:${f.font_family||f.css_font_family};font-weight:${f.font_weight_default||'600'};`"
                                                      x-text="f.name||f.internal_name">
                                                </span>
                                            </label>
                                        </template>
                                    </div>
                                    <div x-show="field.settings.font_family_override"
                                         class="mt-2 rounded-xl border border-[var(--color-border-soft)] bg-[rgba(253,240,213,0.42)] px-4 py-3 text-center text-xl"
                                         :style="`font-family:${field.settings.font_family_override||'inherit'};font-weight:600;`"
                                         x-text="fieldPreviewText(field)||'Amena & Hassan'">
                                    </div>
                                </div>
                                <div class="field-shell">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Weight &amp; style</span>
                                    <div class="flex gap-2">
                                        <select class="field-select flex-1" x-model="field.settings.font_weight">
                                            <option value="400">Regular</option>
                                            <option value="500">Medium</option>
                                            <option value="600">Semibold</option>
                                            <option value="700">Bold</option>
                                            <option value="800">Extra bold</option>
                                        </select>
                                        <button type="button"
                                                class="flex-shrink-0 rounded-xl border px-3 py-2 text-sm transition"
                                                :class="field.settings.font_style === 'italic'
                                                    ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.06)] text-[var(--color-primary-900)] italic'
                                                    : 'border-[var(--color-border-soft)] text-[var(--color-text-soft)]'"
                                                @click="field.settings.font_style = field.settings.font_style === 'italic' ? 'normal' : 'italic'"
                                                title="Toggle italic">
                                            <em class="font-bold not-italic" style="font-style:italic">I</em>
                                        </button>
                                    </div>
                                </div>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Letter spacing</span><input type="number" step="0.01" class="field-input" x-model.number="field.letter_spacing"></label>
                                <label class="field-shell"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Line height</span><input type="number" step="0.01" class="field-input" x-model.number="field.line_height"></label>
                                <label class="field-shell">
                                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Text transform</span>
                                    <select class="field-select" x-model="field.settings.text_transform">
                                        <option value="none">None</option>
                                        <option value="uppercase">Uppercase</option>
                                        <option value="lowercase">Lowercase</option>
                                        <option value="capitalize">Capitalize</option>
                                    </select>
                                </label>
                            </div>

                            <div x-show="currentFieldTab(index) === 'fitting'" class="grid gap-4 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                                <div class="grid gap-4">
                                    <label class="inline-flex items-center justify-between gap-4 rounded-[22px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                        <span>Auto-fit</span>
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="field.settings.auto_fit">
                                        </div>
                                    </label>
                                    <label class="inline-flex items-center justify-between gap-4 rounded-[22px] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                        <span>Multi-line</span>
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" class="h-5 w-5 rounded border-[var(--color-border-soft)] text-[var(--color-primary-900)]" x-model="field.settings.allow_multiline">
                                        </div>
                                    </label>
                                    <label class="field-shell">
                                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Max lines</span>
                                        <input type="number" min="1" max="20" class="field-input" x-model.number="field.settings.max_lines">
                                    </label>
                                    <label class="field-shell">
                                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Overflow behavior</span>
                                        <select class="field-select" x-model="field.settings.overflow_behavior">
                                            <option value="shrink_only">Shrink only</option>
                                            <option value="shrink_then_wrap">Shrink then wrap</option>
                                            <option value="clip">Clip</option>
                                        </select>
                                    </label>
                                </div>

                                <div class="rounded-[24px] border border-[rgba(120,0,0,0.1)] bg-[rgba(253,240,213,0.48)] p-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" :class="fitBadgeClass(field)" x-text="fitBadgeLabel(field)"></span>
                                        <span class="rounded-full bg-white/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--color-secondary-900)]" x-text="fitBadgeDetail(field)"></span>
                                    </div>
                                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-soft)]">
                                        Text auto-fits live between
                                        <span class="font-semibold text-[var(--color-secondary-900)]" x-text="field.font_size_min"></span>px
                                        and
                                        <span class="font-semibold text-[var(--color-secondary-900)]" x-text="field.font_size_max"></span>px.
                                        It will not go outside this range.
                                    </p>
                                    <div class="mt-4 grid gap-3 text-sm text-[var(--color-text-soft)]">
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Current fitted font size:</span> <span x-text="fieldFit(field).fontSize + 'px'"></span></p>
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Estimated safe characters:</span> <span x-text="fieldFit(field).estimatedCharacters"></span></p>
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Text width usage:</span> <span x-text="fieldFit(field).widthUsage + '%'"></span></p>
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Text height usage:</span> <span x-text="fieldFit(field).heightUsage + '%'"></span></p>
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Measured line count:</span> <span x-text="fieldFit(field).lineCount"></span></p>
                                        <p x-show="fieldFit(field).warning" class="font-medium text-[var(--color-primary-900)]" x-text="fieldFit(field).warning"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- ── DATE FORMAT TAB (only for date type) ────────────────────────── --}}
                            <div x-show="currentFieldTab(index) === 'date' && field.field_type === 'date'" class="space-y-4">
                                <template x-if="!field.field_key.includes('date')">
                                    <p class="text-sm text-[var(--color-text-soft)]">Date tab applies to fields whose key contains "date".</p>
                                </template>

                                <template x-if="field.field_key.endsWith('_bangla') || field.field_key.endsWith('_arabic')">
                                    <div class="rounded-[20px] border border-[var(--color-border-soft)] bg-[rgba(120,0,0,0.03)] p-4 space-y-2">
                                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Auto-computed date field</p>
                                        <p class="text-[11px] text-[var(--color-text-soft)]" x-text="field.field_key.endsWith('_bangla') ? 'Auto-filled with Bengali calendar (বঙ্গাব্দ) when customer enters a date.' : 'Auto-filled with Hijri calendar date when customer enters a date.'"></p>
                                        <p class="text-[11px] text-[var(--color-text-soft)]">Position and style using Layout / Typography tabs above. No separate format needed.</p>
                                    </div>
                                </template>

                                <template x-if="field.field_key.includes('date') && !field.field_key.endsWith('_bangla') && !field.field_key.endsWith('_arabic')">
                                    <div class="space-y-3">
                                        <p class="text-xs text-[var(--color-text-soft)]">Format used when displaying this date on the certificate. Bangla/Arabic companion fields are added separately via presets.</p>
                                        <div class="grid gap-2">
                                            <template x-for="fmt in [
                                                {key:'ordinal', label:'20th December 2026, Sunday', hint:'Day(ordinal) Month Year, Weekday'},
                                                {key:'long',    label:'20 December 2026',            hint:'Day Month Year'},
                                                {key:'us',      label:'December 20, 2026',           hint:'Month Day, Year'},
                                                {key:'numeric', label:'20/12/2026',                  hint:'DD/MM/YYYY'},
                                            ]" :key="fmt.key">
                                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition"
                                                       :class="field.settings.date_format === fmt.key
                                                           ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.04)]'
                                                           : 'border-[var(--color-border-soft)] hover:border-[var(--color-primary-900)]'">
                                                    <input type="radio" :value="fmt.key" x-model="field.settings.date_format" class="accent-[var(--color-primary-900)]">
                                                    <span>
                                                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="fmt.label"></span>
                                                        <span class="ml-2 text-[11px] text-[var(--color-text-soft)]" x-text="fmt.hint"></span>
                                                    </span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- ── PREFIX / POSTFIX TAB ────────────────────────────────── --}}
                            <div x-show="currentFieldTab(index) === 'prefix_postfix'" class="space-y-4">
                                <p class="text-xs text-[var(--color-text-soft)]">Prefix/postfix render inline on the same line as the main text. All sizes and weights are relative to the field's rendered values.</p>

                                <template x-for="part in [{key:'prefix',label:'Prefix (before)'},{key:'postfix',label:'Postfix (after)'}]" :key="part.key">
                                    <div class="rounded-[18px] border border-[var(--color-border-soft)] bg-white/80 p-4 space-y-3"
                                         x-data="{
                                             get txt()       { return part.key==='prefix' ? (field.settings.prefix||'')  : (field.settings.postfix||''); },
                                             get offset()    { return part.key==='prefix' ? (Number(field.settings.prefix_size)||0)  : (Number(field.settings.postfix_size)||0); },
                                             get wDelta()    { return part.key==='prefix' ? (Number(field.settings.prefix_weight_delta)||0)  : (Number(field.settings.postfix_weight_delta)||0); },
                                             get italicMode(){ return part.key==='prefix' ? (field.settings.prefix_italic_mode||'auto')  : (field.settings.postfix_italic_mode||'auto'); },
                                             get transform() { return part.key==='prefix' ? (field.settings.prefix_transform||'none') : (field.settings.postfix_transform||'none'); },
                                             get color()     { return part.key==='prefix' ? (field.settings.prefix_color||field.text_color||'#780000') : (field.settings.postfix_color||field.text_color||'#780000'); },
                                             resolvedSize()  { const base = this.fieldFit ? this.fieldFit(field).fontSize : (field.font_size_min||12); return Math.max(6, base + this.offset); },
                                             resolvedWeight(){ const weights=[400,500,600,700,800]; const base=Number(field.settings.font_weight||600); const idx=weights.indexOf(base); return weights[Math.max(0,Math.min(weights.length-1,idx+this.wDelta))]; },
                                             resolvedItalic(){ if(this.italicMode==='italic') return true; if(this.italicMode==='normal') return false; return (field.settings.font_style||'normal')==='italic'; },
                                         }">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="part.label"></p>
                                            <span class="text-[10px] text-[var(--color-text-soft)]"
                                                  x-text="`≈ ${resolvedSize()}px · w${resolvedWeight()} · ${resolvedItalic()?'italic':'normal'}`"></span>
                                        </div>

                                        <label class="field-shell">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Text</span>
                                            <input class="field-input"
                                                   :value="txt"
                                                   @input="part.key==='prefix' ? field.settings.prefix=$event.target.value : field.settings.postfix=$event.target.value"
                                                   :placeholder="part.key==='prefix' ? 'e.g. THIS AGREEMENT MADE ON THE' : 'e.g. AH'">
                                        </label>

                                        <div class="flex flex-wrap items-center gap-2">
                                            {{-- Size offset: relative ±px --}}
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="rounded-lg border border-[var(--color-border-soft)] px-2 py-1.5 text-xs hover:border-[var(--color-primary-900)]"
                                                        @click="part.key==='prefix' ? field.settings.prefix_size=(Number(field.settings.prefix_size)||0)-1 : field.settings.postfix_size=(Number(field.settings.postfix_size)||0)-1">
                                                    A−
                                                </button>
                                                <span class="w-16 text-center text-xs text-[var(--color-text-soft)]"
                                                      x-text="offset===0 ? 'same' : (offset>0 ? '+'+offset+'px' : offset+'px')"></span>
                                                <button type="button" class="rounded-lg border border-[var(--color-border-soft)] px-2 py-1.5 text-xs hover:border-[var(--color-primary-900)]"
                                                        @click="part.key==='prefix' ? field.settings.prefix_size=(Number(field.settings.prefix_size)||0)+1 : field.settings.postfix_size=(Number(field.settings.postfix_size)||0)+1">
                                                    A+
                                                </button>
                                            </div>

                                            {{-- Weight: relative bolder/inherit/lighter --}}
                                            <select class="rounded-xl border border-[var(--color-border-soft)] px-2 py-1.5 text-xs"
                                                    :value="wDelta"
                                                    @change="part.key==='prefix' ? field.settings.prefix_weight_delta=Number($event.target.value) : field.settings.postfix_weight_delta=Number($event.target.value)">
                                                <option value="-2">Thinner −2</option>
                                                <option value="-1">Lighter −1</option>
                                                <option value="0">Inherit weight</option>
                                                <option value="1">Bolder +1</option>
                                                <option value="2">Bolder +2</option>
                                            </select>

                                            {{-- Italic: auto (inherit) / italic / normal --}}
                                            <div class="flex rounded-xl border border-[var(--color-border-soft)] overflow-hidden">
                                                <template x-for="mode in [{k:'auto',l:'Auto'},{k:'italic',l:'I'},{k:'normal',l:'—I'}]" :key="mode.k">
                                                    <button type="button"
                                                            class="px-2.5 py-1.5 text-xs transition"
                                                            :class="italicMode===mode.k ? 'bg-[var(--color-primary-900)] text-white' : 'text-[var(--color-text-soft)] hover:bg-[rgba(0,48,73,0.05)]'"
                                                            @click="part.key==='prefix' ? field.settings.prefix_italic_mode=mode.k : field.settings.postfix_italic_mode=mode.k"
                                                            x-text="mode.l"></button>
                                                </template>
                                            </div>

                                            {{-- Text transform --}}
                                            <select class="rounded-xl border border-[var(--color-border-soft)] px-2 py-1.5 text-xs"
                                                    :value="transform"
                                                    @change="part.key==='prefix'?field.settings.prefix_transform=$event.target.value:field.settings.postfix_transform=$event.target.value">
                                                <option value="none">None</option>
                                                <option value="uppercase">UPPER</option>
                                                <option value="lowercase">lower</option>
                                                <option value="capitalize">Capitalize</option>
                                            </select>

                                            {{-- Color --}}
                                            <input type="color" class="h-7 w-9 cursor-pointer rounded border border-[var(--color-border-soft)]"
                                                   :value="color"
                                                   @input="part.key==='prefix'?field.settings.prefix_color=$event.target.value:field.settings.postfix_color=$event.target.value">

                                            {{-- Reset to 0 --}}
                                            <button type="button" class="text-[10px] text-[var(--color-text-soft)] hover:text-[var(--color-primary-900)]"
                                                    @click="part.key==='prefix' ? (field.settings.prefix_size=0, field.settings.prefix_weight_delta=0, field.settings.prefix_italic_mode='auto') : (field.settings.postfix_size=0, field.settings.postfix_weight_delta=0, field.settings.postfix_italic_mode='auto')"
                                                    title="Reset to field defaults">↺</button>
                                        </div>

                                        {{-- Live preview --}}
                                        <p class="rounded-xl border border-[var(--color-border-soft)] bg-[rgba(253,240,213,0.42)] px-4 py-2"
                                           :style="`font-size:${resolvedSize()}px;font-weight:${resolvedWeight()};font-style:${resolvedItalic()?'italic':'normal'};text-transform:${transform};color:${color};`"
                                           x-text="txt||'Preview text'"></p>
                                    </div>
                                </template>
                            </div>

                            <div x-show="currentFieldTab(index) === 'tools'" class="flex flex-wrap gap-3">
                                <button type="button" class="button-ghost" @click="focusField(field.id)">Focus on canvas</button>
                                <button type="button" class="button-ghost" @click="moveField(field.id, -1)">Move up</button>
                                <button type="button" class="button-ghost" @click="moveField(field.id, 1)">Move down</button>
                                <button type="button" class="button-ghost" @click="duplicateField(field.id)">Duplicate field</button>
                                <button type="button" class="button-ghost" @click="confirmDeleteField(field.id)">Delete field</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

            <div class="mt-6 flex justify-center">
                <button type="button" class="button-ghost" @click="selectedPreset ? addField(selectedPreset) : addField()">Add field</button>
            </div>
        </aside>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Font presets</p>
                <h3 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">Fonts for this template</h3>
                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Manage the global font library to add, edit, or remove fonts. Then toggle which are available here.</p>
            </div>
            <a href="{{ route('admin.personalization.fonts.index') }}" class="button-ghost text-sm">Manage font library →</a>
        </div>
        {{-- ── FONT SELECTION (demo cards + enable/disable) ───────────────── --}}
        <div class="mt-8 rounded-[28px] border border-[var(--color-border-soft)] bg-[rgba(255,253,249,0.86)] p-6 shadow-[0_10px_24px_rgba(0,48,73,0.04)]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Font selection</p>
                    <h3 class="mt-1 text-xl font-semibold text-[var(--color-secondary-900)]">Choose fonts available to customers</h3>
                    <p class="mt-1 text-sm text-[var(--color-text-soft)]">All active fonts appear on the storefront. Click to toggle.</p>
                </div>
                {{-- Import from starter presets --}}
                <details class="group">
                    <summary class="button-ghost cursor-pointer list-none text-sm">+ Import presets</summary>
                    <div class="absolute z-10 mt-2 w-80 rounded-[20px] border border-[var(--color-border-soft)] bg-white p-4 shadow-[0_20px_48px_rgba(0,48,73,0.14)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Starter presets</p>
                        <div class="mt-3 space-y-2">
                            @foreach ($starterFontPresets as $sp)
                                <button type="button"
                                        class="w-full rounded-xl border border-[var(--color-border-soft)] bg-[rgba(253,240,213,0.40)] px-4 py-2.5 text-left transition hover:border-[var(--color-primary-900)]"
                                        @click="addFontFromPreset(@js($sp))">
                                    <span class="block text-sm font-semibold" style="font-family: {{ $sp['font_family'] ?? 'inherit' }}">{{ $sp['name'] }}</span>
                                    <span class="text-xs text-[var(--color-text-soft)]">{{ $sp['font_family'] ?? '' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <template x-for="font in sortedFonts()" :key="font.id">
                    <div class="relative cursor-pointer select-none rounded-[20px] border-2 p-4 transition"
                         :class="font.is_active !== false ? 'border-[var(--color-primary-900)] bg-[rgba(120,0,0,0.03)]' : 'border-[var(--color-border-soft)] bg-white/60 opacity-60'"
                         @click="font.is_active = font.is_active === false ? true : false">
                        {{-- Check indicator --}}
                        <span class="absolute right-3 top-3 flex h-5 w-5 items-center justify-center rounded-full transition"
                              :class="font.is_active !== false ? 'bg-[var(--color-primary-900)] text-white' : 'border border-[var(--color-border-soft)] bg-white'">
                            <svg x-show="font.is_active !== false" class="h-3 w-3" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5"><path d="M2 6l3 3 5-5"/></svg>
                        </span>
                        {{-- Font name in its own typeface --}}
                        <p class="pr-6 text-base font-semibold leading-snug text-[var(--color-secondary-900)]"
                           :style="`font-family:${font.font_family||font.css_font_family};font-weight:${font.font_weight_default||'600'};`"
                           x-text="font.name||font.internal_name"></p>
                        {{-- Sample text --}}
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]"
                           :style="`font-family:${font.font_family||font.css_font_family};font-weight:${font.font_weight_default||'600'};letter-spacing:${font.letter_spacing_default||0}px;`"
                           x-text="font.preview_sample_text||'بسم الله الرحمن الرحيم'"></p>
                        <p class="mt-1.5 truncate text-[10px] text-[var(--color-text-soft)] opacity-70" x-text="font.font_family||font.css_font_family"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <input type="hidden" name="fields_payload" value="">
    <input type="hidden" name="fonts_payload"  value="">

    <div class="flex flex-wrap justify-end gap-3">
        <button type="submit" class="button-ghost" name="save_mode" value="draft">Save draft</button>
        <button type="submit" class="button-primary" name="save_mode" value="template">Save template</button>
    </div>
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('nikahTemplateEditor', (config) => ({
        ...config,
        canvasZoom: 1,
        showSafeAreas: true,
        showFieldBounds: true,
        showAdvancedAssets: false,
        selectedAsset: 'base',
        draftMode: false,
        assetPreviewUrls: {},
        collectiveTextColor: '#780000',
        collectiveFontWeight: '600',
        openFieldIndex: null,
        activeFieldId: null,
        fieldTabs: [
            { key: 'basic', label: 'Basic' },
            { key: 'prefix_postfix', label: 'Pre / Post' },
            { key: 'layout', label: 'Layout' },
            { key: 'typography', label: 'Typography' },
            { key: 'fitting', label: 'Fitting' },
            { key: 'date', label: 'Date' },
            { key: 'tools', label: 'Tools' },
        ],
        activeTabs: {},
        fontAdvancedOpen: {},
        selectedPreset: '',
        draggingContext: null,
        measureContext: null,
        previewTextScale: 0.55,
        nextFieldId: 1,
        nextFontId: 1,
        fieldPresets: [
            { key: 'bride_name',             label: 'Bride name',              type: 'text',   position_x: 50, position_y: 27, width: 56, height: 14, transform: 'uppercase', multiline: false },
            { key: 'groom_name',             label: 'Groom name',              type: 'text',   position_x: 50, position_y: 43, width: 56, height: 14, transform: 'uppercase', multiline: false },
            { key: 'ceremony_date',          label: 'Ceremony date',           type: 'date',   position_x: 50, position_y: 61, width: 40, height: 12, transform: 'uppercase', multiline: true  },
            { key: 'ceremony_date_bangla',   label: 'Bangla date (বঙ্গাব্দ)',   type: 'text',   position_x: 50, position_y: 67, width: 50, height: 10, transform: 'none',      multiline: false },
            { key: 'ceremony_date_arabic',   label: 'Arabic date (Hijri)',      type: 'text',   position_x: 50, position_y: 74, width: 50, height: 10, transform: 'none',      multiline: false },
            { key: 'venue',                  label: 'Venue',                   type: 'text',   position_x: 50, position_y: 81, width: 50, height: 14, transform: 'uppercase', multiline: true  },
            { key: 'quotation',              label: 'Quotation',               type: 'text',   position_x: 50, position_y: 88, width: 70, height: 14, transform: 'none',      multiline: true  },
            { key: 'static_text',            label: 'Static text',             type: 'static', position_x: 50, position_y: 20, width: 80, height: 10, transform: 'uppercase', multiline: true  },
        ],
        init() {
            this.fields = this.initialFields.map((field, index) => this.normalizedField(field, index));
            this.fonts = this.initialFonts.map((font, index) => this.normalizedFont(font, index));
            this.nextFieldId = (Math.max(0, ...this.fields.map((field) => Number(field.id) || 0)) || 0) + 1;
            this.nextFontId = (Math.max(0, ...this.fonts.map((font) => Number(font.id) || 0)) || 0) + 1;
            this.openFieldIndex = this.fields.length ? 0 : null;
            this.activeFieldId = this.fields.length ? this.fields[0].id : null;
            if (this.fields[0]) {
                this.collectiveTextColor = this.fields[0].text_color || '#780000';
                this.collectiveFontWeight = this.fields[0].settings.font_weight || '600';
            }
        },
        normalizedField(field, index) {
            // Auto-detect type from settings or field_key
            const autoType = (() => {
                if (field.settings?.field_type) return field.settings.field_type;
                const k = field.field_key ?? '';
                if (k.endsWith('_bangla') || k.endsWith('_arabic')) return 'text';
                if (k.includes('date')) return 'date';
                return 'text';
            })();
            return {
                id: field.id ?? this.nextFieldId + index,
                field_type: autoType,
                label: field.label ?? '',
                field_key: field.field_key ?? '',
                placeholder: field.placeholder ?? '',
                help_text: field.help_text ?? '',
                default_value: field.default_value ?? '',
                preview_sample_value: field.preview_sample_value ?? '',
                is_required: Boolean(field.is_required ?? false),
                max_length: Number(field.max_length ?? 100),
                min_length: Number(field.min_length ?? 0),
                font_size_min: Number(field.font_size_min ?? this.previewRules.default_min_font_size ?? 18),
                font_size_max: Number(field.font_size_max ?? this.previewRules.default_max_font_size ?? 40),
                line_height: Number(field.line_height ?? this.previewRules.default_line_height ?? 1.2),
                letter_spacing: Number(field.letter_spacing ?? this.previewRules.default_letter_spacing ?? 0),
                text_align: field.text_align ?? 'center',
                text_color: field.text_color ?? '#780000',
                position_x: Number(field.position_x ?? 50),
                position_y: Number(field.position_y ?? 50),
                width: Number(field.width ?? 56),
                height: Number(field.height ?? 14),
                rotation: Number(field.rotation ?? 0),
                z_index: Number(field.z_index ?? index),
                settings: {
                    // Core fitting/typography settings
                    auto_fit: Boolean(field.settings?.auto_fit ?? this.previewRules.auto_fit_enabled ?? true),
                    allow_multiline: Boolean(field.settings?.allow_multiline ?? this.previewRules.allow_multiline ?? true),
                    max_lines: Number(field.settings?.max_lines ?? 3),
                    overflow_behavior: field.settings?.overflow_behavior ?? 'shrink_then_wrap',
                    font_family_override: field.settings?.font_family_override ?? '',
                    font_weight: String(field.settings?.font_weight ?? '600'),
                    font_style:  field.settings?.font_style ?? 'normal',
                    text_transform: field.settings?.text_transform ?? 'none',
                    date_format: field.settings?.date_format ?? 'long',
                    prefix:              field.settings?.prefix ?? '',
                    prefix_size:         Number(field.settings?.prefix_size ?? 0),
                    prefix_weight_delta: Number(field.settings?.prefix_weight_delta ?? 0),
                    prefix_italic_mode:  field.settings?.prefix_italic_mode ?? 'auto',
                    prefix_color:        field.settings?.prefix_color ?? '',
                    prefix_transform:    field.settings?.prefix_transform ?? 'none',
                    postfix:             field.settings?.postfix ?? '',
                    postfix_size:        Number(field.settings?.postfix_size ?? 0),
                    postfix_weight_delta:Number(field.settings?.postfix_weight_delta ?? 0),
                    postfix_italic_mode: field.settings?.postfix_italic_mode ?? 'auto',
                    postfix_color:       field.settings?.postfix_color ?? '',
                    postfix_transform:   field.settings?.postfix_transform ?? 'none',
                },
            };
        },
        normalizedFont(font, index) {
            return {
                id: font.id ?? this.nextFontId + index,
                name: font.name ?? '',
                internal_name: font.internal_name ?? '',
                css_font_family: font.css_font_family ?? '',
                font_family: font.font_family ?? font.css_font_family ?? '',
                font_source_type: font.font_source_type ?? 'local',
                font_source_value: font.font_source_value ?? '',
                category: font.category ?? 'Minimal Sans',
                style_type: font.style_type ?? font.category ?? 'Minimal Sans',
                supported_use: font.supported_use ?? 'all',
                preview_label: font.preview_label ?? '',
                preview_sample_text: font.preview_sample_text ?? font.preview_label ?? 'Amena & Hassan',
                font_weight_default: String(font.font_weight_default ?? '600'),
                font_style_default: font.font_style_default ?? 'normal',
                letter_spacing_default: Number(font.letter_spacing_default ?? 0),
                line_height_default: Number(font.line_height_default ?? 1.2),
                text_transform_default: font.text_transform_default ?? 'none',
                recommended_for: font.recommended_for ?? 'all',
                is_default: Boolean(font.is_default ?? index === 0),
                is_active: Boolean(font.is_active ?? true),
                sort_order: Number(font.sort_order ?? index),
            };
        },
        sortedFonts() {
            return [...this.fonts].sort((a, b) => Number(a.sort_order) - Number(b.sort_order));
        },
        stageStyle() {
            const width = Math.max(1, Number(this.exportRatioWidth) || 9);
            const height = Math.max(1, Number(this.exportRatioHeight) || 13);

            return `aspect-ratio:${width}/${height}; max-width: 980px;`;
        },
        get canvasArtworkUrl() {
            return this.assetValue('baseTemplateUrl') || this.assetValue('previewImageUrl') || '';
        },
        assetValue(key) {
            return this.assetPreviewUrls[key] || this[key] || '';
        },
        resetView() {
            this.canvasZoom = 1;
            this.showSafeAreas = true;
            this.showFieldBounds = true;
        },
        advancedAssets() {
            return [
                {
                    key: 'preview',
                    title: 'Preview image',
                    help: 'Optional polished display version',
                    value: this.assetValue('previewImageUrl'),
                    stateKey: 'previewImageUrl',
                    uploadName: 'preview_image_upload',
                    urlName: 'preview_image_url',
                    removeName: 'remove_preview_image',
                    removeFlag: this.removePreviewImage,
                },
                {
                    key: 'mask',
                    title: 'Mask image',
                    help: 'Optional advanced clipping/render helper',
                    value: this.assetValue('maskImageUrl'),
                    stateKey: 'maskImageUrl',
                    uploadName: 'mask_image_upload',
                    urlName: 'mask_image_url',
                    removeName: 'remove_mask_image',
                    removeFlag: this.removeMaskImage,
                },
            ];
        },
        selectAsset(key) {
            this.selectedAsset = key;
        },
        swapAssetPreview(key, event) {
            const file = event.target.files?.[0];

            if (! file) return;

            if (typeof this.assetPreviewUrls[key] === 'string' && this.assetPreviewUrls[key].startsWith('blob:')) {
                URL.revokeObjectURL(this.assetPreviewUrls[key]);
            }

            this.assetPreviewUrls[key] = URL.createObjectURL(file);
            if (key === 'baseTemplateUrl') this.removeBaseTemplate = false;
            if (key === 'previewImageUrl') this.removePreviewImage = false;
            if (key === 'maskImageUrl') this.removeMaskImage = false;
        },
        syncAssetUrl(key, value) {
            if (typeof this.assetPreviewUrls[key] === 'string' && this.assetPreviewUrls[key].startsWith('blob:')) {
                URL.revokeObjectURL(this.assetPreviewUrls[key]);
            }

            this.assetPreviewUrls[key] = '';
            this[key] = value;
            if (key === 'baseTemplateUrl') this.removeBaseTemplate = false;
            if (key === 'previewImageUrl') this.removePreviewImage = false;
            if (key === 'maskImageUrl') this.removeMaskImage = false;
        },
        clearAsset(key) {
            if (typeof this.assetPreviewUrls[key] === 'string' && this.assetPreviewUrls[key].startsWith('blob:')) {
                URL.revokeObjectURL(this.assetPreviewUrls[key]);
            }

            this.assetPreviewUrls[key] = '';
            this[key] = '';
            if (key === 'baseTemplateUrl') this.removeBaseTemplate = true;
            if (key === 'previewImageUrl') this.removePreviewImage = true;
            if (key === 'maskImageUrl') this.removeMaskImage = true;
        },
        currentFieldTab(index) {
            return this.activeTabs[index] ?? 'basic';
        },
        setFieldTab(index, tab) {
            this.activeTabs[index] = tab;
        },
        toggleField(index) {
            this.openFieldIndex = this.openFieldIndex === index ? null : index;
            if (this.fields[index]) {
                this.activeFieldId = this.fields[index].id;
            }

            if (this.openFieldIndex === null) {
                this.activeFieldId = null;
            }
        },
        clearSelection() {
            this.openFieldIndex = null;
            this.activeFieldId = null;
        },
        focusField(fieldId, options = {}) {
            const index = this.fields.findIndex((field) => field.id === fieldId);

            if (index === -1) return;

            this.activeFieldId = fieldId;
            this.openFieldIndex = index;

            if (options.scroll) {
                this.$nextTick(() => this.focusAccordionById(fieldId));
            }
        },
        focusAccordionById(fieldId) {
            const target = document.getElementById(`field-accordion-${fieldId}`);
            target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
        handleCanvasKeydown(event) {
            if (! this.activeFieldId) return;

            const movementKey = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];

            if (! movementKey.includes(event.key)) {
                return;
            }

            const field = this.fields.find((item) => item.id === this.activeFieldId);

            if (! field) return;

            const step = event.shiftKey ? 1 : event.altKey ? 0.1 : 0.25;

            if (event.key === 'ArrowUp') field.position_y -= step;
            if (event.key === 'ArrowDown') field.position_y += step;
            if (event.key === 'ArrowLeft') field.position_x -= step;
            if (event.key === 'ArrowRight') field.position_x += step;

            this.constrainField(field);
        },
        sortedFields() {
            return [...this.fields]
                .filter((field) => field.label || field.field_key)
                .sort((a, b) => Number(a.z_index) - Number(b.z_index));
        },
        activeFieldSummary() {
            const field = this.fields.find((item) => item.id === this.activeFieldId);
            if (! field) return 'No field selected';

            return `${field.label || field.field_key || 'Field'} is active on the canvas`;
        },
        activeFieldMessage() {
            const field = this.fields.find((item) => item.id === this.activeFieldId);
            if (! field) return 'Select a field to fine tune safe area placement, typography, and fitting behaviour.';

            const fit = this.fieldFit(field);

            return `${fit.message}. Zone ${Number(field.width).toFixed(0)}% × ${Number(field.height).toFixed(0)}% at ${Number(field.position_x).toFixed(0)} / ${Number(field.position_y).toFixed(0)}.`;
        },
        addField(presetKey = null, explicitType = null) {
            const preset = this.fieldPresets.find((item) => item.key === (presetKey || this.selectedPreset));
            const nextIndex = this.fields.length;
            const resolvedType = explicitType || preset?.type || 'text';

            // For bangla/arabic companion fields, copy visual settings from the parent date field
            const presetKeyResolved = presetKey || this.selectedPreset;
            let inheritedSettings = {};
            if (presetKeyResolved === 'ceremony_date_bangla' || presetKeyResolved === 'ceremony_date_arabic') {
                const parentDate = this.fields.find((f) => f.field_key === 'ceremony_date');
                if (parentDate) {
                    inheritedSettings = {
                        text_color:  parentDate.text_color,
                        font_size_min: parentDate.font_size_min,
                        font_size_max: parentDate.font_size_max,
                        line_height:   parentDate.line_height,
                        letter_spacing: parentDate.letter_spacing,
                        settings_font_family_override: parentDate.settings?.font_family_override,
                        settings_font_weight: parentDate.settings?.font_weight,
                        settings_font_style:  parentDate.settings?.font_style,
                        settings_text_transform: parentDate.settings?.text_transform,
                    };
                }
            }

            const field = this.normalizedField({
                id: this.nextFieldId++,
                label: preset ? preset.label : `New field ${nextIndex + 1}`,
                field_key: preset ? preset.key : `new_field_${nextIndex + 1}`,
                preview_sample_value: preset ? this.sampleValue(preset.key, preset.label) : 'Sample text',
                is_required: resolvedType !== 'static' && (preset ? true : false),
                position_x: preset ? preset.position_x : 50,
                position_y: preset ? preset.position_y : 50,
                width: preset ? preset.width : 52,
                height: preset ? preset.height : 14,
                text_color: inheritedSettings.text_color || '#780000',
                font_size_min: inheritedSettings.font_size_min,
                font_size_max: inheritedSettings.font_size_max,
                line_height:   inheritedSettings.line_height,
                letter_spacing: inheritedSettings.letter_spacing,
                settings: {
                    field_type: resolvedType,
                    auto_fit: true,
                    allow_multiline: preset ? preset.multiline : true,
                    max_lines: preset && ! preset.multiline ? 1 : 3,
                    overflow_behavior: preset && ! preset.multiline ? 'shrink_only' : 'shrink_then_wrap',
                    font_family_override: inheritedSettings.settings_font_family_override || '',
                    font_weight: inheritedSettings.settings_font_weight || '600',
                    font_style:  inheritedSettings.settings_font_style  || 'normal',
                    text_transform: inheritedSettings.settings_text_transform ?? (preset?.transform ?? 'none'),
                },
                z_index: nextIndex,
            }, nextIndex);

            this.fields.push(field);
            this.selectedPreset = '';
            this.focusField(field.id);
        },
        moveField(fieldId, direction) {
            const index = this.fields.findIndex((field) => field.id === fieldId);
            const nextIndex = index + direction;

            if (index === -1 || nextIndex < 0 || nextIndex >= this.fields.length) {
                return;
            }

            const [field] = this.fields.splice(index, 1);
            this.fields.splice(nextIndex, 0, field);
            this.fields.forEach((item, position) => {
                item.z_index = position;
            });
            this.focusField(field.id);
        },
        duplicateField(fieldId) {
            const field = this.fields.find((item) => item.id === fieldId);
            if (! field) return;

            const copy = JSON.parse(JSON.stringify(field));
            copy.id = this.nextFieldId++;
            copy.label = `${field.label || 'Field'} Copy`;
            copy.field_key = `${field.field_key || 'field'}_copy_${copy.id}`;
            copy.position_x = Math.min(90, Number(copy.position_x) + 4);
            copy.position_y = Math.min(90, Number(copy.position_y) + 4);
            copy.z_index = this.fields.length;

            this.fields.push(copy);
            this.focusField(copy.id);
        },
        confirmDeleteField(fieldId) {
            const field = this.fields.find((item) => item.id === fieldId);
            if (! field) return;

            const label = field.label || field.field_key || 'this field';

            if (! window.confirm(`Delete ${label}?`)) {
                return;
            }

            this.deleteField(fieldId);
        },
        deleteField(fieldId) {
            if (this.fields.length === 1) {
                return;
            }

            const index = this.fields.findIndex((field) => field.id === fieldId);
            if (index === -1) return;

            this.fields.splice(index, 1);
            this.fields.forEach((field, position) => {
                field.z_index = position;
            });

            if (this.fields[index]) {
                this.focusField(this.fields[index].id);
            } else if (this.fields[index - 1]) {
                this.focusField(this.fields[index - 1].id);
            } else {
                this.activeFieldId = null;
                this.openFieldIndex = null;
            }
        },
        addFont() {
            this.fonts.push(this.normalizedFont({
                id: this.nextFontId++,
                name: `Font preset ${this.fonts.length + 1}`,
                internal_name: `font_preset_${this.fonts.length + 1}`,
                css_font_family: '"Poppins", sans-serif',
                font_family: '"Poppins", sans-serif',
                font_source_type: 'local',
                font_source_value: '',
                category: 'Minimal Sans',
                style_type: 'Minimal Sans',
                supported_use: 'all',
                preview_label: 'Ceremony preview',
                preview_sample_text: 'Amena & Hassan',
                font_weight_default: '600',
                font_style_default: 'normal',
                letter_spacing_default: 0,
                line_height_default: 1.2,
                text_transform_default: 'none',
                recommended_for: 'all',
                is_default: this.fonts.length === 0,
                is_active: true,
                sort_order: this.fonts.length,
            }, this.fonts.length));
        },
        addFontFromPreset(preset) {
            // Check not already added by name
            if (this.fonts.some(f => f.name === preset.name)) return;
            this.fonts.push(this.normalizedFont({
                id: this.nextFontId++,
                ...preset,
                is_active: true,
                sort_order: this.fonts.length,
            }, this.fonts.length));
        },
        duplicateFont(fontId) {
            const font = this.fonts.find((item) => item.id === fontId);
            if (! font) return;

            const copy = JSON.parse(JSON.stringify(font));
            copy.id = this.nextFontId++;
            copy.name = `${font.name || 'Font preset'} Copy`;
            copy.internal_name = `${font.internal_name || 'font_preset'}_copy_${copy.id}`;
            copy.is_default = false;
            copy.sort_order = this.fonts.length;

            this.fonts.push(copy);
        },
        confirmDeleteFont(fontId) {
            const font = this.fonts.find((item) => item.id === fontId);
            if (! font) return;

            if (! window.confirm(`Delete ${font.name || 'this font preset'}?`)) {
                return;
            }

            this.deleteFont(fontId);
        },
        deleteFont(fontId) {
            if (this.fonts.length === 1) return;
            this.fonts = this.fonts.filter((font) => font.id !== fontId);
            this.fonts.forEach((font, index) => {
                if (! Number.isFinite(Number(font.sort_order))) {
                    font.sort_order = index;
                }
            });
            if (! this.fonts.some((font) => font.is_default) && this.fonts[0]) {
                this.fonts[0].is_default = true;
            }
        },
        setDefaultFont(fontId) {
            this.fonts.forEach((font) => {
                font.is_default = font.id === fontId;
            });
        },
        toggleFontAdvanced(fontId) {
            this.fontAdvancedOpen[fontId] = ! this.fontAdvancedOpen[fontId];
        },
        applyAllAlignment(value) {
            this.fields.forEach((field) => {
                field.text_align = value;
            });
        },
        applyAllTextColor() {
            this.fields.forEach((field) => {
                field.text_color = this.collectiveTextColor || '#780000';
            });
        },
        adjustAllFontSizes(delta) {
            this.fields.forEach((field) => {
                field.font_size_min = Math.max(8, Math.min(200, Number(field.font_size_min || 8) + delta));
                field.font_size_max = Math.max(8, Math.min(240, Number(field.font_size_max || 8) + delta));
                this.syncFontBounds(field, 'max');
            });
        },
        applyAllFontWeight() {
            this.fields.forEach((field) => {
                field.settings.font_weight = this.collectiveFontWeight || '600';
            });
        },
        setAllAutoFit(enabled) {
            this.fields.forEach((field) => {
                field.settings.auto_fit = enabled;
            });
        },
        setAllMultiline(enabled) {
            this.fields.forEach((field) => {
                field.settings.allow_multiline = enabled;
                if (! enabled) {
                    field.settings.max_lines = 1;
                    if (field.settings.overflow_behavior === 'shrink_then_wrap') {
                        field.settings.overflow_behavior = 'shrink_only';
                    }
                }
            });
        },
        applyGlobalTypeDefaults() {
            this.fields.forEach((field) => {
                field.font_size_min = Number(this.previewRules.default_min_font_size || 18);
                field.font_size_max = Number(this.previewRules.default_max_font_size || 40);
                field.line_height = Number(this.previewRules.default_line_height || 1.2);
                field.letter_spacing = Number(this.previewRules.default_letter_spacing || 0);
                field.settings.auto_fit = Boolean(this.previewRules.auto_fit_enabled ?? true);
                this.syncFontBounds(field, 'max');
            });
        },
        serializableFields() {
            return this.fields.map((field, index) => ({
                label: field.label ?? '',
                field_key: field.field_key ?? '',
                placeholder: field.placeholder ?? '',
                help_text: field.help_text ?? '',
                default_value: field.default_value ?? '',
                preview_sample_value: field.preview_sample_value ?? '',
                is_required: field.is_required ? 1 : 0,
                min_length: Number(field.min_length ?? 0),
                max_length: Number(field.max_length ?? 100),
                font_size_min: Number(field.font_size_min ?? this.previewRules.default_min_font_size ?? 18),
                font_size_max: Number(field.font_size_max ?? this.previewRules.default_max_font_size ?? 40),
                line_height: Number(field.line_height ?? this.previewRules.default_line_height ?? 1.2),
                letter_spacing: Number(field.letter_spacing ?? this.previewRules.default_letter_spacing ?? 0),
                text_align: field.text_align ?? 'center',
                text_color: field.text_color ?? '#780000',
                position_x: Number(field.position_x ?? 50),
                position_y: Number(field.position_y ?? 50),
                width: Number(field.width ?? 56),
                height: Number(field.height ?? 14),
                rotation: Number(field.rotation ?? 0),
                z_index: Number(field.z_index ?? index),
                settings: {
                    auto_fit: field.settings?.auto_fit ? 1 : 0,
                    allow_multiline: field.settings?.allow_multiline ? 1 : 0,
                    max_lines: Number(field.settings?.max_lines ?? 3),
                    overflow_behavior: field.settings?.overflow_behavior ?? 'shrink_then_wrap',
                    font_family_override: field.settings?.font_family_override ?? '',
                    font_weight: field.settings?.font_weight ?? '600',
                    font_style:  field.settings?.font_style ?? 'normal',
                    text_transform: field.settings?.text_transform ?? 'none',
                    field_type: field.field_type ?? 'text',
                    date_format: field.settings?.date_format ?? 'long',
                    prefix:              field.settings?.prefix ?? '',
                    prefix_size:         Number(field.settings?.prefix_size ?? 0),
                    prefix_weight_delta: Number(field.settings?.prefix_weight_delta ?? 0),
                    prefix_italic_mode:  field.settings?.prefix_italic_mode ?? 'auto',
                    prefix_color:        field.settings?.prefix_color ?? '',
                    prefix_transform:    field.settings?.prefix_transform ?? 'none',
                    postfix:             field.settings?.postfix ?? '',
                    postfix_size:        Number(field.settings?.postfix_size ?? 0),
                    postfix_weight_delta:Number(field.settings?.postfix_weight_delta ?? 0),
                    postfix_italic_mode: field.settings?.postfix_italic_mode ?? 'auto',
                    postfix_color:       field.settings?.postfix_color ?? '',
                    postfix_transform:   field.settings?.postfix_transform ?? 'none',
                },
            }));
        },
        serializableFonts() {
            return this.sortedFonts().map((font, index) => ({
                name: font.name ?? '',
                internal_name: font.internal_name ?? '',
                preview_label: font.preview_label ?? '',
                css_font_family: font.css_font_family ?? font.font_family ?? '',
                font_family: font.font_family ?? font.css_font_family ?? '',
                font_source_type: font.font_source_type ?? 'local',
                font_source_value: font.font_source_value ?? '',
                category: font.category ?? 'Minimal Sans',
                style_type: font.style_type ?? (font.category ?? 'Minimal Sans'),
                supported_use: font.supported_use ?? 'all',
                preview_sample_text: font.preview_sample_text ?? font.preview_label ?? font.name ?? '',
                font_weight_default: font.font_weight_default ?? '600',
                font_style_default: font.font_style_default ?? 'normal',
                letter_spacing_default: Number(font.letter_spacing_default ?? 0),
                line_height_default: Number(font.line_height_default ?? 1.2),
                text_transform_default: font.text_transform_default ?? 'none',
                recommended_for: font.recommended_for ?? 'all',
                is_default: font.is_default ? 1 : 0,
                is_active: font.is_active ? 1 : 0,
                sort_order: Number(font.sort_order ?? index),
            }));
        },
        fieldName(index, key) {
            return `fields[${index}][${key}]`;
        },
        fieldSettingName(index, key) {
            return `fields[${index}][settings][${key}]`;
        },
        fontName(index, key) {
            return `fonts[${index}][${key}]`;
        },
        beginDragById(fieldId, event) {
            const index = this.fields.findIndex((field) => field.id === fieldId);
            const field = this.fields[index];
            if (! field) return;

            this.activeFieldId = fieldId;
            this.openFieldIndex = index;
            this.draggingContext = {
                mode: 'move',
                fieldId,
                startX: event.clientX,
                startY: event.clientY,
                originX: Number(field.position_x),
                originY: Number(field.position_y),
            };
        },
        beginResizeById(fieldId, direction, event) {
            const index = this.fields.findIndex((field) => field.id === fieldId);
            const field = this.fields[index];
            if (! field) return;

            this.activeFieldId = fieldId;
            this.openFieldIndex = index;
            this.draggingContext = {
                mode: 'resize',
                direction,
                fieldId,
                startX: event.clientX,
                startY: event.clientY,
                originX: Number(field.position_x),
                originY: Number(field.position_y),
                originWidth: Number(field.width),
                originHeight: Number(field.height),
            };
        },
        pointerMove(event) {
            if (! this.draggingContext || ! this.$refs.previewStage) return;

            const field = this.fields.find((item) => item.id === this.draggingContext.fieldId);
            if (! field) return;

            const rect = this.$refs.previewStage.getBoundingClientRect();
            const deltaX = ((event.clientX - this.draggingContext.startX) / rect.width) * 100;
            const deltaY = ((event.clientY - this.draggingContext.startY) / rect.height) * 100;

            if (this.draggingContext.mode === 'move') {
                field.position_x = this.draggingContext.originX + deltaX;
                field.position_y = this.draggingContext.originY + deltaY;
            } else {
                const horizontal = this.draggingContext.direction.includes('right') ? 1 : -1;
                const vertical = this.draggingContext.direction.includes('bottom') ? 1 : -1;
                field.width = this.draggingContext.originWidth + (deltaX * horizontal);
                field.height = this.draggingContext.originHeight + (deltaY * vertical);
                field.position_x = this.draggingContext.originX + ((deltaX * horizontal) / 2);
                field.position_y = this.draggingContext.originY + ((deltaY * vertical) / 2);
            }

            this.constrainField(field);
        },
        pointerUp() {
            this.draggingContext = null;
        },
        constrainField(field) {
            field.width = Math.min(95, Math.max(10, Number(field.width)));
            field.height = Math.min(80, Math.max(8, Number(field.height)));
            field.position_x = Math.min(100 - (field.width / 2), Math.max(field.width / 2, Number(field.position_x)));
            field.position_y = Math.min(100 - (field.height / 2), Math.max(field.height / 2, Number(field.position_y)));
        },
        syncFontBounds(field, source) {
            field.font_size_min = Math.max(8, Number(field.font_size_min || 8));
            field.font_size_max = Math.max(8, Number(field.font_size_max || 8));

            if (source === 'min' && field.font_size_min > field.font_size_max) {
                field.font_size_max = field.font_size_min;
            }

            if (source === 'max' && field.font_size_max < field.font_size_min) {
                field.font_size_min = field.font_size_max;
            }
        },
        canvasFieldShellStyle(field) {
            const left = Math.max(0, Number(field.position_x) - (Number(field.width) / 2));
            const top = Math.max(0, Number(field.position_y) - (Number(field.height) / 2));

            return `left:${left}%; top:${top}%; width:${field.width}%; height:${field.height}%; transform: rotate(${field.rotation}deg); z-index:${field.z_index + 2};`;
        },
        canvasFieldClass(field) {
            const selected = this.activeFieldId === field.id;

            if (! this.showFieldBounds && ! selected) {
                return '';
            }

            return selected
                ? 'border border-dashed border-[rgba(193,18,31,0.38)] bg-[rgba(255,255,255,0.55)] shadow-[0_18px_40px_rgba(0,48,73,0.12)]'
                : 'border border-dashed border-[rgba(0,48,73,0.16)] bg-[rgba(255,255,255,0.38)]';
        },
        canvasFieldInnerStyle(field) {
            const alignMap = { start: 'left', center: 'center', end: 'right' };

            return `text-align:${alignMap[field.text_align] || 'center'};`;
        },
        canvasFieldTextStyle(field) {
            const fit = this.fieldFit(field);
            const previewFontSize = Math.max(8, Number((fit.fontSize * this.previewTextScale).toFixed(2)));

            return `color:${field.text_color||'#780000'}; font-size:${previewFontSize}px; letter-spacing:${Number(field.letter_spacing||0)}px; line-height:${Number(field.line_height||1.2)}; font-weight:${field.settings.font_weight||'600'}; font-style:${field.settings.font_style||'normal'}; font-family:${field.settings.font_family_override||'"Poppins", sans-serif'}; text-transform:${field.settings.text_transform||'none'};`;
        },
        fieldPreviewText(field) {
            // Explicit access (no optional chaining) so Alpine tracks prefix/postfix as reactive deps
            const pre  = (field.settings && field.settings.prefix)  || '';
            const post = (field.settings && field.settings.postfix) || '';
            const wrap = s => {
                const parts = [];
                if (pre)  parts.push(pre);
                parts.push(s || '…');
                if (post) parts.push(post);
                return parts.filter(Boolean).join(' ') || 'Sample text';
            };

            // Date type: show formatted date sample
            if ((field.field_type === 'date' || field.field_key.includes('date')) && !field.field_key.endsWith('_bangla') && !field.field_key.endsWith('_arabic')) {
                return wrap(this.formatDateSample(this.previewData?.ceremony_date ?? '12 December 2026', field.settings?.date_format ?? 'long'));
            }

            // Static type: show default_value (reactive to typing)
            if (field.field_type === 'static') {
                return wrap(field.default_value || 'Static text...');
            }

            // Auto-date companions
            if (field.field_key.endsWith('_bangla')) return '৫ পৌষ ১৪৩৩';
            if (field.field_key.endsWith('_arabic')) return '19th Jumada al-Awwal 1447 AH';

            // Known previewData keys
            const mapped = {
                bride_name:    this.previewData.bride_name,
                groom_name:    this.previewData.groom_name,
                ceremony_date: this.previewData.ceremony_date,
                venue:         this.previewData.venue,
            }[field.field_key];
            if (mapped) return wrap(mapped);

            // Custom/new fields: show default_value when typed, else preview_sample_value / placeholder
            const content = field.default_value || field.preview_sample_value || field.placeholder || 'Sample text';
            return wrap(content);
        },
        formatDateSample(dateStr, fmt) {
            const EN_M    = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            const EN_DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const ordinal = n => { const s=['th','st','nd','rd'], v=n%100; return n+(s[(v-20)%10]||s[v]||s[0]); };
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const day = d.getDate(), month = d.getMonth(), year = d.getFullYear();
            const mn = EN_M[month], dayName = EN_DAYS[d.getDay()];
            if (fmt === 'ordinal') return `${ordinal(day)} ${mn} ${year}, ${dayName}`;
            if (fmt === 'us')      return `${mn} ${day}, ${year}`;
            if (fmt === 'numeric') return `${String(day).padStart(2,'0')}/${String(month+1).padStart(2,'0')}/${year}`;
            return `${day} ${mn} ${year}`;
        },
        sampleValue(fieldKey, fallback = '') {
            const map = {
                bride_name: this.previewData.bride_name,
                groom_name: this.previewData.groom_name,
                ceremony_date: this.previewData.ceremony_date,
                venue: this.previewData.venue,
            };

            // Auto-date companion fields
            if (fieldKey.endsWith('_bangla')) return '৫ পৌষ ১৪৩৩';
            if (fieldKey.endsWith('_arabic')) return '19th Jumada al-Awwal 1447 AH';

            return map[fieldKey] || fallback || 'Sample text';
        },
        getMeasureContext() {
            if (! this.measureContext) {
                this.measureContext = document.createElement('canvas').getContext('2d');
            }

            return this.measureContext;
        },
        applyTransform(text, transform) {
            if (transform === 'uppercase') return String(text).toUpperCase();
            if (transform === 'lowercase') return String(text).toLowerCase();
            if (transform === 'capitalize') return String(text).replace(/\b\w/g, (char) => char.toUpperCase());

            return String(text);
        },
        wrapText(text, maxWidth, fontSize, field) {
            const ctx = this.getMeasureContext();
            ctx.font = `${field.settings.font_weight || '600'} ${fontSize}px ${field.settings.font_family_override || 'Poppins, sans-serif'}`;
            const letterSpacing = Number(field.letter_spacing || 0);
            const words = String(text).split(/\s+/).filter(Boolean);
            const lines = [];
            let current = '';

            const measure = (value) => ctx.measureText(value).width + Math.max(0, value.length - 1) * letterSpacing;

            if (! field.settings.allow_multiline) {
                return { lines: [String(text)], maxWidth: measure(String(text)) };
            }

            words.forEach((word) => {
                const next = current ? `${current} ${word}` : word;
                if (measure(next) <= maxWidth || ! current) {
                    current = next;
                } else {
                    lines.push(current);
                    current = word;
                }
            });

            if (current) lines.push(current);

            if (! lines.length) lines.push(String(text));

            return {
                lines,
                maxWidth: Math.max(...lines.map((line) => measure(line))),
            };
        },
        fieldFit(field) {
            const stage = this.$refs.previewStage;
            const stageWidth = stage?.clientWidth || 900;
            const stageHeight = stage?.clientHeight || Math.round((900 * (this.exportRatioHeight || 13)) / (this.exportRatioWidth || 9));
            const zoneWidth = Math.max(48, stageWidth * (Number(field.width) / 100) - 18);
            const zoneHeight = Math.max(32, stageHeight * (Number(field.height) / 100) - 18);
            const minFont = Math.max(8, Number(field.font_size_min || this.previewRules.default_min_font_size || 18));
            const maxFont = Math.max(minFont, Number(field.font_size_max || this.previewRules.default_max_font_size || 40));
            const sample = this.applyTransform(this.fieldPreviewText(field), field.settings.text_transform);
            const maxLines = Math.max(1, Number(field.settings.max_lines || 1));
            const autoFit = Boolean(field.settings.auto_fit);
            const behavior = field.settings.overflow_behavior || 'shrink_then_wrap';

            const evaluateAt = (size) => {
                const effectiveField = (behavior === 'clip' || behavior === 'shrink_only')
                    ? { ...field, settings: { ...field.settings, allow_multiline: false } }
                    : field;
                const layout = behavior === 'clip'
                    ? { lines: [sample], maxWidth: this.wrapText(sample, zoneWidth, size, effectiveField).maxWidth }
                    : this.wrapText(sample, zoneWidth, size, effectiveField);
                const lineHeightPx = size * Number(field.line_height || 1.2);
                const lineCount = behavior === 'clip' ? 1 : layout.lines.length;
                const fitsWidth = behavior === 'clip' ? true : layout.maxWidth <= zoneWidth;
                const fitsLines = behavior === 'clip' ? true : lineCount <= maxLines;
                const fitsHeight = (lineCount * lineHeightPx) <= zoneHeight;

                return {
                    fits: fitsWidth && fitsLines && fitsHeight,
                    lineCount,
                    maxWidth: layout.maxWidth,
                    totalHeight: lineCount * lineHeightPx,
                };
            };

            if (! autoFit) {
                const fixed = evaluateAt(maxFont);

            return {
                status: fixed.fits ? 'fits' : 'overflow',
                fontSize: maxFont,
                estimatedCharacters: Math.max(8, Math.floor(zoneWidth / Math.max(6, maxFont * 0.48)) * Math.max(1, maxLines)),
                widthUsage: Math.min(999, Math.round((fixed.maxWidth / zoneWidth) * 100)),
                heightUsage: Math.min(999, Math.round((fixed.totalHeight / zoneHeight) * 100)),
                lineCount: fixed.lineCount,
                warning: fixed.fits ? '' : 'Preview sample exceeds the zone at the fixed size.',
                message: fixed.fits ? `Fits at ${maxFont}px` : 'Overflow risk',
                };
            }

            for (let size = maxFont; size >= minFont; size -= 1) {
                const result = evaluateAt(size);
                if (result.fits) {
                    return {
                        status: size === maxFont ? 'fits' : 'shrunk',
                        fontSize: size,
                        estimatedCharacters: Math.max(8, Math.floor(zoneWidth / Math.max(6, size * 0.48)) * Math.max(1, maxLines)),
                        widthUsage: Math.min(999, Math.round((result.maxWidth / zoneWidth) * 100)),
                        heightUsage: Math.min(999, Math.round((result.totalHeight / zoneHeight) * 100)),
                        lineCount: result.lineCount,
                        warning: size === maxFont ? '' : 'Preview sample needed to shrink to fit the current zone.',
                        message: size === maxFont ? `Fits at ${size}px` : `Shrunk to ${size}px`,
                    };
                }
            }

            return {
                status: 'overflow',
                fontSize: minFont,
                estimatedCharacters: Math.max(8, Math.floor(zoneWidth / Math.max(6, minFont * 0.48)) * Math.max(1, maxLines)),
                widthUsage: 100,
                heightUsage: 100,
                lineCount: maxLines,
                warning: 'Overflow risk: increase the zone size, allow more lines, or shorten the sample.',
                message: 'Overflow risk',
            };
        },
        fitBadgeLabel(field) {
            const fit = this.fieldFit(field);
            if (fit.status === 'fits') return 'Fits';
            if (fit.status === 'shrunk') return 'Shrunk';
            return 'Overflow risk';
        },
        fitBadgeDetail(field) {
            return this.fieldFit(field).message;
        },
        fitBadgeClass(field) {
            const fit = this.fieldFit(field);
            if (fit.status === 'fits') return 'bg-[rgba(102,155,188,0.14)] text-[var(--color-secondary-900)]';
            if (fit.status === 'shrunk') return 'bg-[rgba(253,240,213,0.95)] text-[var(--color-primary-900)]';
            return 'bg-[rgba(193,18,31,0.12)] text-[var(--color-primary-900)]';
        },
    }));
});
</script>
