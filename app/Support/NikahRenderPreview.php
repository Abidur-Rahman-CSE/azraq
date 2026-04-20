<?php

namespace App\Support;

use App\Models\OrderItem;
use App\Models\PersonalizationFont;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Models\Product;

class NikahRenderPreview
{
    public static function buildForProduct(
        Product $product,
        array $personalization = [],
        ?PersonalizationFont $font = null,
        ?PersonalizationTemplate $template = null,
        ?PersonalizationMockup $mockup = null,
    ): ?array {
        $product->loadMissing([
            'personalizationTemplate.fields',
            'personalizationTemplate.fonts',
            'personalizationMockups.map',
        ]);

        $template ??= $product->personalizationTemplate;

        if (! $template) {
            return null;
        }

        $template->loadMissing(['fields', 'fonts', 'mockups.map']);

        $mockup ??= $product->personalizationMockups
            ->where('is_active', true)
            ->firstWhere('pivot.is_default', true)
            ?? $product->personalizationMockups->where('is_active', true)->first()
            ?? $template->mockups->where('is_active', true)->first()
            ?? $template->mockups->first();

        $textLayers = $template->fields->map(function ($field) use ($personalization) {
            $value = $personalization[$field->field_key] ?? $field->default_value ?? $field->preview_sample_value ?? $field->placeholder;

            return [
                'key' => $field->field_key,
                'label' => $field->label,
                'value' => $value,
                'placeholder' => $field->placeholder,
                'x' => (float) $field->position_x,
                'y' => (float) $field->position_y,
                'width' => (float) $field->width,
                'height' => (float) $field->height,
                'rotation' => (float) $field->rotation,
                'align' => $field->text_align,
                'color' => $field->text_color,
                'line_height' => (float) $field->line_height,
                'letter_spacing' => (float) $field->letter_spacing,
                'font_size_min' => (int) $field->font_size_min,
                'font_size_max' => (int) $field->font_size_max,
                'z_index' => (int) ($field->z_index ?? 1),
            ];
        })->values()->all();

        return [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'base_image_url' => $template->base_template_url,
                'preview_image_url' => $template->preview_image_url ?: $template->base_template_url,
                'ratio_width' => (int) ($template->export_ratio_width ?: 9),
                'ratio_height' => (int) ($template->export_ratio_height ?: 13),
                'safe_zone_notes' => $template->safe_zone_notes,
                'instructions' => $template->instructions,
            ],
            'font' => [
                'id' => $font?->id,
                'name' => $font?->name,
                'css_font_family' => $font?->css_font_family,
            ],
            'flat' => [
                'image_url' => $template->preview_image_url ?: $template->base_template_url,
                'text_layers' => $textLayers,
            ],
            'mockup' => $mockup ? [
                'id' => $mockup->id,
                'title' => $mockup->title,
                'base_image_url' => $mockup->base_image_url,
                'overlay_image_url' => $mockup->overlay_image_url,
                'mask_image_url' => $mockup->mask_image_url,
                'render_mode' => $mockup->render_mode,
                'map' => $mockup->map ? [
                    'top_left_x' => (float) $mockup->map->top_left_x,
                    'top_left_y' => (float) $mockup->map->top_left_y,
                    'top_right_x' => (float) $mockup->map->top_right_x,
                    'top_right_y' => (float) $mockup->map->top_right_y,
                    'bottom_right_x' => (float) $mockup->map->bottom_right_x,
                    'bottom_right_y' => (float) $mockup->map->bottom_right_y,
                    'bottom_left_x' => (float) $mockup->map->bottom_left_x,
                    'bottom_left_y' => (float) $mockup->map->bottom_left_y,
                    'opacity' => (float) ($mockup->map->opacity ?? 0.95),
                    'shadow_strength' => (float) ($mockup->map->shadow_strength ?? 0.18),
                    'highlight_strength' => (float) ($mockup->map->highlight_strength ?? 0.12),
                ] : null,
            ] : null,
        ];
    }

    public static function buildForOrderItem(
        OrderItem $item,
        ?PersonalizationTemplate $template = null,
        ?PersonalizationMockup $mockup = null,
    ): ?array {
        $item->loadMissing([
            'product.personalizationTemplate.fields',
            'product.personalizationTemplate.fonts',
            'product.personalizationMockups.map',
        ]);

        $meta = $item->line_item_meta ?? [];
        $fontName = $meta['font'] ?? null;
        $product = $item->product;

        if (! $product) {
            return null;
        }

        $font = $template?->fonts?->firstWhere('name', $fontName)
            ?? $product->personalizationTemplate?->fonts?->firstWhere('name', $fontName);

        return self::buildForProduct(
            $product,
            $meta['personalization'] ?? [],
            $font,
            $template,
            $mockup,
        );
    }
}
