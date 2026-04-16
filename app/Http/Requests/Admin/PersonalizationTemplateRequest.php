<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PersonalizationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'preview_image_url' => ['required', 'url', 'max:2048'],
            'instructions' => ['nullable', 'string'],
            'proof_note_label' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'preview_rules.safe_scale' => ['nullable', 'boolean'],
            'preview_rules.allow_multiline' => ['nullable', 'boolean'],
            'render_rules.export_format' => ['nullable', 'string', 'max:50'],
            'render_rules.proof_required' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['nullable', 'string', 'max:255'],
            'fields.*.field_key' => ['nullable', 'string', 'max:255'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.help_text' => ['nullable', 'string', 'max:255'],
            'fields.*.default_value' => ['nullable', 'string', 'max:255'],
            'fields.*.max_length' => ['nullable', 'integer', 'min:1', 'max:500'],
            'fields.*.min_length' => ['nullable', 'integer', 'min:0', 'max:500'],
            'fields.*.font_size_min' => ['nullable', 'integer', 'min:8', 'max:200'],
            'fields.*.font_size_max' => ['nullable', 'integer', 'min:8', 'max:240'],
            'fields.*.line_height' => ['nullable', 'numeric', 'min:0.5', 'max:3'],
            'fields.*.letter_spacing' => ['nullable', 'numeric', 'min:-10', 'max:30'],
            'fields.*.text_align' => ['nullable', 'in:start,center,end'],
            'fields.*.text_color' => ['nullable', 'string', 'max:20'],
            'fields.*.position_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.position_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.width' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fields.*.height' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fonts' => ['nullable', 'array'],
            'fonts.*.name' => ['nullable', 'string', 'max:255'],
            'fonts.*.css_font_family' => ['nullable', 'string', 'max:255'],
            'fonts.*.preview_label' => ['nullable', 'string', 'max:255'],
            'fonts.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}
