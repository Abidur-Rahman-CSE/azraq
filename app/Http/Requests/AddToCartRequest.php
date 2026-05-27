<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use App\Models\PersonalizationFont;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'custom_text' => ['nullable', 'string', 'max:120'],
            'font_id' => ['nullable', 'integer', 'exists:personalization_fonts,id'],
            'font_selection' => ['nullable', 'array'],
            'font_selection.*' => ['nullable', 'integer', 'exists:personalization_fonts,id'],
            'mockup_id' => ['nullable', 'integer', 'exists:personalization_mockups,id'],
            'proof_note' => ['nullable', 'string', 'max:500'],
            'personalization' => ['nullable', 'array'],
            'personalization.*' => ['nullable', 'string', 'max:500'],
            'bundle_selections' => ['nullable', 'array'],
            'bundle_selections.*' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Product|null $product */
            $product = $this->route('product');

            if (! $product) {
                return;
            }

            if ($this->filled('variant_id')) {
                $variant = ProductVariant::query()->find($this->integer('variant_id'));

                if (! $variant || $variant->product_id !== $product->id) {
                    $validator->errors()->add('variant_id', 'The selected variant does not belong to this product.');
                }
            }

            if ($this->filled('font_id')) {
                $font = PersonalizationFont::query()->find($this->integer('font_id'));
                $templateId = $product->personalizationTemplate?->id;

                if (! $font || ! $templateId || $font->personalization_template_id !== $templateId || ! $font->is_active) {
                    $validator->errors()->add('font_id', 'The selected font is not available for this product.');
                }
            }

            collect($this->input('font_selection', []))
                ->filter(fn ($fontId) => filled($fontId))
                ->each(function ($fontId, $fieldKey) use ($validator, $product): void {
                    $font = PersonalizationFont::query()->find((int) $fontId);
                    $templateId = $product->personalizationTemplate?->id;

                    if (! $font || ! $templateId || $font->personalization_template_id !== $templateId || ! $font->is_active) {
                        $validator->errors()->add('font_selection.'.$fieldKey, 'The selected font is not available for this field.');
                    }
                });

            if ($this->filled('mockup_id')) {
                $mockup = PersonalizationMockup::query()->find($this->integer('mockup_id'));
                $allowedMockupIds = $product->personalizationMockups()
                    ->where('is_active', true)
                    ->pluck('personalization_mockups.id');

                if (! $mockup || ! $allowedMockupIds->contains($mockup->id)) {
                    $validator->errors()->add('mockup_id', 'The selected mockup is not available for this product.');
                }
            }

            if ($product->type === ProductType::LightCustomizable) {
                collect($product->personalization_fields_blueprint ?? [])
                    ->filter(fn ($field) => (bool) ($field['is_required'] ?? $field['required'] ?? false))
                    ->each(function (array $field) use ($validator): void {
                        $fieldKey = $field['field_key'] ?? $field['key'] ?? null;

                        if (! $fieldKey) {
                            return;
                        }

                        if (! filled($this->input('personalization.'.$fieldKey))) {
                            $validator->errors()->add(
                                'personalization.'.$fieldKey,
                                ($field['label'] ?? 'This field').' is required.',
                            );
                        }
                    });
            }
        });
    }
}
