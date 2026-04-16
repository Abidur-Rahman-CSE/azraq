@php($isEdit = $template->exists)
@php($fieldRows = old('fields', $template->fields->map(fn ($field) => $field->toArray())->all() ?: array_fill(0, 4, [])))
@php($fontRows = old('fonts', $template->fonts->map(fn ($font) => $font->toArray())->all() ?: array_fill(0, 3, [])))
@php($previewRules = old('preview_rules', $template->preview_rules ?? []))
@php($renderRules = old('render_rules', $template->render_rules ?? []))

<form method="POST" action="{{ $isEdit ? route('admin.personalization.templates.update', $template) : route('admin.personalization.templates.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product</span>
            <select name="product_id" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id', $template->product_id) === (string) $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Template name</span>
            <input type="text" name="name" value="{{ old('name', $template->name) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Preview image URL</span>
            <input type="url" name="preview_image_url" value="{{ old('preview_image_url', $template->preview_image_url) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Instructions</span>
            <textarea name="instructions" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ old('instructions', $template->instructions) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Proof note label</span>
            <input type="text" name="proof_note_label" value="{{ old('proof_note_label', $template->proof_note_label) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <div class="grid gap-3">
            <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                <input type="hidden" name="preview_rules[safe_scale]" value="0">
                <input type="checkbox" name="preview_rules[safe_scale]" value="1" @checked((bool) ($previewRules['safe_scale'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                Safe scaling
            </label>
            <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                <input type="hidden" name="preview_rules[allow_multiline]" value="0">
                <input type="checkbox" name="preview_rules[allow_multiline]" value="1" @checked((bool) ($previewRules['allow_multiline'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                Allow multiline
            </label>
            <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                <input type="hidden" name="render_rules[proof_required]" value="0">
                <input type="checkbox" name="render_rules[proof_required]" value="1" @checked((bool) ($renderRules['proof_required'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                Proof required
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Export format</span>
                <input type="text" name="render_rules[export_format]" value="{{ $renderRules['export_format'] ?? 'png' }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            </label>
            <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                Active
            </label>
        </div>
    </div>

    <div class="surface-card p-6">
        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Text zones</h3>
        <div class="mt-6 space-y-4">
            @foreach ($fieldRows as $index => $field)
                <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] p-4 md:grid-cols-4">
                    <input type="text" name="fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}" placeholder="Label" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fields[{{ $index }}][field_key]" value="{{ $field['field_key'] ?? '' }}" placeholder="field_key" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fields[{{ $index }}][placeholder]" value="{{ $field['placeholder'] ?? '' }}" placeholder="Placeholder" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fields[{{ $index }}][help_text]" value="{{ $field['help_text'] ?? '' }}" placeholder="Help text" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" name="fields[{{ $index }}][max_length]" value="{{ $field['max_length'] ?? 30 }}" placeholder="Max length" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" name="fields[{{ $index }}][font_size_min]" value="{{ $field['font_size_min'] ?? 18 }}" placeholder="Min font" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" name="fields[{{ $index }}][font_size_max]" value="{{ $field['font_size_max'] ?? 36 }}" placeholder="Max font" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fields[{{ $index }}][text_color]" value="{{ $field['text_color'] ?? '#780000' }}" placeholder="#780000" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" step="0.01" name="fields[{{ $index }}][position_x]" value="{{ $field['position_x'] ?? 50 }}" placeholder="X %" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" step="0.01" name="fields[{{ $index }}][position_y]" value="{{ $field['position_y'] ?? 50 }}" placeholder="Y %" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" step="0.01" name="fields[{{ $index }}][width]" value="{{ $field['width'] ?? 70 }}" placeholder="Width %" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <select name="fields[{{ $index }}][text_align]" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        @foreach (['start' => 'Left', 'center' => 'Center', 'end' => 'Right'] as $value => $label)
                            <option value="{{ $value }}" @selected(($field['text_align'] ?? 'center') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    <div class="surface-card p-6">
        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Font options</h3>
        <div class="mt-6 space-y-4">
            @foreach ($fontRows as $index => $font)
                <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] p-4 md:grid-cols-4">
                    <input type="text" name="fonts[{{ $index }}][name]" value="{{ $font['name'] ?? '' }}" placeholder="Font name" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fonts[{{ $index }}][css_font_family]" value="{{ $font['css_font_family'] ?? '' }}" placeholder="CSS font family" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="fonts[{{ $index }}][preview_label]" value="{{ $font['preview_label'] ?? '' }}" placeholder="Preview label" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 text-sm text-[var(--color-secondary-900)]">
                        <input type="hidden" name="fonts[{{ $index }}][is_default]" value="0">
                        <input type="checkbox" name="fonts[{{ $index }}][is_default]" value="1" @checked((bool) ($font['is_default'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Default
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save changes' : 'Create template' }}</button>
        <a href="{{ route('admin.personalization.templates.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
