<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PersonalizationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_template_upload' => ['nullable', 'image', 'max:10240'],
            'preview_image_upload' => ['nullable', 'image', 'max:10240'],
            'mask_image_upload' => ['nullable', 'image', 'max:10240'],
            'base_template_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidAssetReference($value)) {
                    $fail('The '.$attribute.' must be a valid URL or local asset path.');
                }
            }],
            'preview_image_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidAssetReference($value)) {
                    $fail('The '.$attribute.' must be a valid URL or local asset path.');
                }
            }],
            'mask_image_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidAssetReference($value)) {
                    $fail('The '.$attribute.' must be a valid URL or local asset path.');
                }
            }],
            'remove_base_template' => ['nullable', 'boolean'],
            'remove_preview_image' => ['nullable', 'boolean'],
            'remove_mask_image' => ['nullable', 'boolean'],
            'export_ratio_width' => ['nullable', 'integer', 'min:1', 'max:100'],
            'export_ratio_height' => ['nullable', 'integer', 'min:1', 'max:100'],
            'instructions' => ['nullable', 'string'],
            'safe_zone_notes' => ['nullable', 'string'],
            'proof_note_label' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'preview_rules.safe_scale' => ['nullable', 'boolean'],
            'preview_rules.allow_multiline' => ['nullable', 'boolean'],
            'preview_rules.safe_editing' => ['nullable', 'boolean'],
            'preview_rules.default_min_font_size' => ['nullable', 'integer', 'min:8', 'max:200'],
            'preview_rules.default_max_font_size' => ['nullable', 'integer', 'min:8', 'max:240'],
            'preview_rules.default_line_height' => ['nullable', 'numeric', 'min:0.5', 'max:3'],
            'preview_rules.default_letter_spacing' => ['nullable', 'numeric', 'min:-10', 'max:30'],
            'preview_rules.auto_fit_enabled' => ['nullable', 'boolean'],
            'preview_rules.estimated_longest_safe_field' => ['nullable', 'string', 'max:255'],
            'render_rules.export_format' => ['nullable', 'string', 'max:50'],
            'render_rules.proof_required' => ['nullable', 'boolean'],
            'render_rules.export_ratio_lock' => ['nullable', 'boolean'],
            'render_rules.export_size' => ['nullable', 'string', 'max:50'],
            'preview_data_presets.bride_name' => ['nullable', 'string', 'max:255'],
            'preview_data_presets.groom_name' => ['nullable', 'string', 'max:255'],
            'preview_data_presets.ceremony_date' => ['nullable', 'string', 'max:255'],
            'preview_data_presets.venue' => ['nullable', 'string', 'max:255'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['nullable', 'string', 'max:255'],
            'fields.*.field_key' => ['nullable', 'string', 'max:255'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.help_text' => ['nullable', 'string', 'max:255'],
            'fields.*.default_value' => ['nullable', 'string', 'max:255'],
            'fields.*.is_required' => ['nullable', 'boolean'],
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
            'fields.*.rotation' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'fields.*.z_index' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'fields.*.preview_sample_value' => ['nullable', 'string', 'max:255'],
            'fields.*.settings.auto_fit' => ['nullable', 'boolean'],
            'fields.*.settings.allow_multiline' => ['nullable', 'boolean'],
            'fields.*.settings.max_lines' => ['nullable', 'integer', 'min:1', 'max:20'],
            'fields.*.settings.overflow_behavior' => ['nullable', 'in:shrink_only,shrink_then_wrap,clip'],
            'fields.*.settings.font_family_override' => ['nullable', 'string', 'max:255'],
            'fields.*.settings.font_weight' => ['nullable', 'in:400,500,600,700,800'],
            'fields.*.settings.text_transform' => ['nullable', 'in:none,uppercase,lowercase,capitalize'],
            // Date-specific settings
            'fields.*.settings.date_format' => ['nullable', 'in:long,us,numeric'],
            'fields.*.settings.auto_bangla' => ['nullable', 'boolean'],
            'fields.*.settings.bangla_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.settings.bangla_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.settings.bangla_width' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fields.*.settings.bangla_height' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fields.*.settings.bangla_color' => ['nullable', 'string', 'max:20'],
            'fields.*.settings.bangla_font_size_min' => ['nullable', 'integer', 'min:6', 'max:120'],
            'fields.*.settings.bangla_font_size_max' => ['nullable', 'integer', 'min:6', 'max:200'],
            'fields.*.settings.auto_arabic' => ['nullable', 'boolean'],
            'fields.*.settings.arabic_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.settings.arabic_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fields.*.settings.arabic_width' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fields.*.settings.arabic_height' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'fields.*.settings.arabic_color' => ['nullable', 'string', 'max:20'],
            'fields.*.settings.arabic_font_size_min' => ['nullable', 'integer', 'min:6', 'max:120'],
            'fields.*.settings.arabic_font_size_max' => ['nullable', 'integer', 'min:6', 'max:200'],
            'fonts' => ['nullable', 'array'],
            'fonts.*.name' => ['nullable', 'string', 'max:255'],
            'fonts.*.internal_name' => ['nullable', 'string', 'max:255'],
            'fonts.*.css_font_family' => ['nullable', 'string', 'max:255'],
            'fonts.*.font_family' => ['nullable', 'string', 'max:255'],
            'fonts.*.font_source_type' => ['nullable', 'in:google,local,uploaded'],
            'fonts.*.font_source_value' => ['nullable', 'string', 'max:2048'],
            'fonts.*.category' => ['nullable', 'string', 'max:255'],
            'fonts.*.style_type' => ['nullable', 'string', 'max:255'],
            'fonts.*.supported_use' => ['nullable', 'string', 'max:255'],
            'fonts.*.preview_label' => ['nullable', 'string', 'max:255'],
            'fonts.*.preview_sample_text' => ['nullable', 'string', 'max:255'],
            'fonts.*.font_weight_default' => ['nullable', 'string', 'max:10'],
            'fonts.*.font_style_default' => ['nullable', 'in:normal,italic'],
            'fonts.*.letter_spacing_default' => ['nullable', 'numeric', 'min:-10', 'max:30'],
            'fonts.*.line_height_default' => ['nullable', 'numeric', 'min:0.5', 'max:3'],
            'fonts.*.text_transform_default' => ['nullable', 'in:none,uppercase,lowercase,capitalize'],
            'fonts.*.recommended_for' => ['nullable', 'string', 'max:255'],
            'fonts.*.is_default' => ['nullable', 'boolean'],
            'fonts.*.is_active' => ['nullable', 'boolean'],
            'fonts.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        foreach (['fields', 'fonts'] as $key) {
            $payloadKey = "{$key}_payload";
            $payload = $this->input($payloadKey);

            if (! is_string($payload) || $payload === '') {
                continue;
            }

            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $merged[$key] = $decoded;
            }
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $template = $this->route('template');
            $hasUploadedBase = $this->file('base_template_upload') !== null;
            $hasIncomingBaseUrl = filled($this->input('base_template_url'));
            $hasExistingBaseUrl = filled($template?->base_template_url) && ! $this->boolean('remove_base_template');

            if (! $hasUploadedBase && ! $hasIncomingBaseUrl && ! $hasExistingBaseUrl) {
                $validator->errors()->add('base_template_upload', 'A base template image is required before the template can be saved.');
            }
        });
    }

    private function isValidAssetReference(mixed $value): bool
    {
        if (! filled($value)) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        if (str_starts_with($value, '/') || str_starts_with($value, 'storage/')) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
