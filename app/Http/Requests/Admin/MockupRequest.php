<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MockupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mockup = $this->route('mockup');

        return [
            'personalization_template_id' => ['required', 'exists:personalization_templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('personalization_mockups', 'slug')->ignore($mockup?->id)],
            'render_mode' => ['required', Rule::in(['flat_fit', 'perspective_quad', 'masked_perspective'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],

            'base_image_upload' => ['nullable', 'image', 'max:10240'],
            'mask_image_upload' => ['nullable', 'image', 'max:5120'],
            'overlay_image_upload' => ['nullable', 'image', 'max:5120'],
            'thumb_image_upload' => ['nullable', 'image', 'max:5120'],

            'base_image_url' => ['nullable', 'string', 'max:2048'],
            'mask_image_url' => ['nullable', 'string', 'max:2048'],
            'overlay_image_url' => ['nullable', 'string', 'max:2048'],
            'thumb_image_url' => ['nullable', 'string', 'max:2048'],
            'remove_base_image' => ['nullable', 'boolean'],
            'remove_mask_image' => ['nullable', 'boolean'],
            'remove_overlay_image' => ['nullable', 'boolean'],
            'remove_thumb_image' => ['nullable', 'boolean'],

            'map.map_type' => ['nullable', 'string', 'max:50'],
            'map.fit_mode' => ['nullable', Rule::in(['contain', 'cover', 'stretch'])],
            'map.top_left_x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.top_left_y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.top_right_x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.top_right_y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.bottom_right_x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.bottom_right_y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.bottom_left_x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.bottom_left_y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.normalized_coordinates' => ['nullable', 'boolean'],
            'map.object_position_x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.object_position_y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.manual_rotation' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'map.shadow_strength' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.highlight_strength' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'map.opacity' => ['nullable', 'numeric', 'min:0.1', 'max:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $mockup = $this->route('mockup');
            $hasUploadedBase = $this->file('base_image_upload') !== null;
            $hasIncomingBaseUrl = filled($this->input('base_image_url'));
            $hasExistingBaseUrl = filled($mockup?->base_image_url) && ! $this->boolean('remove_base_image');

            if (! $hasUploadedBase && ! $hasIncomingBaseUrl && ! $hasExistingBaseUrl) {
                $validator->errors()->add('base_image_upload', 'A base image is required before the mockup can be saved.');
            }
        });
    }
}
