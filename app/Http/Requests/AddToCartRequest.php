<?php

namespace App\Http\Requests;

use App\Models\PersonalizationFont;
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
            'proof_note' => ['nullable', 'string', 'max:500'],
            'personalization' => ['nullable', 'array'],
            'personalization.*' => ['nullable', 'string', 'max:500'],
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

                if (! $font || ! $templateId || $font->personalization_template_id !== $templateId) {
                    $validator->errors()->add('font_id', 'The selected font is not available for this product.');
                }
            }
        });
    }
}
