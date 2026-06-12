<?php

namespace App\Support;

use App\Models\OrderItem;
use App\Models\PersonalizationFont;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use App\Support\MockupZoneNormalizer;

class NikahRenderPreview
{
    public static function buildForProduct(
        Product $product,
        array $personalization = [],
        ?PersonalizationFont $font = null,
        array|PersonalizationTemplate|null $fieldFonts = [],
        PersonalizationTemplate|PersonalizationMockup|null $template = null,
        ?PersonalizationMockup $mockup = null,
    ): ?array {
        if ($fieldFonts instanceof PersonalizationTemplate || $fieldFonts === null) {
            $mockup = $template instanceof PersonalizationMockup ? $template : $mockup;
            $template = $fieldFonts;
            $fieldFonts = [];
        }

        if ($template instanceof PersonalizationMockup) {
            $mockup = $template;
            $template = null;
        }

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

        $textLayers = $template->fields->map(function ($field) use ($personalization, $fieldFonts, $font) {
            $value = $personalization[$field->field_key] ?? $field->default_value ?? $field->preview_sample_value ?? $field->placeholder;
            $selectedFieldFont = $fieldFonts[$field->field_key] ?? null;
            $isNameFontField = str($field->field_key)->contains(['bride', 'groom']);
            $resolvedFont = $selectedFieldFont instanceof PersonalizationFont
                ? $selectedFieldFont
                : ($isNameFontField ? $font : null);
            $settings = $field->settings ?? [];

            if ($resolvedFont) {
                $settings['font_family_override'] = $resolvedFont->resolved_font_family;
                $settings['font_weight'] = $resolvedFont->font_weight_default ?: ($settings['font_weight'] ?? '600');
                $settings['font_style'] = $resolvedFont->font_style_default ?: ($settings['font_style'] ?? 'normal');
                $settings['text_transform'] = $resolvedFont->text_transform_default ?: ($settings['text_transform'] ?? 'none');
            }

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
                'settings' => $settings,
            ];
        })->values()->all();

        $normalizedMap = $mockup?->map
            ? MockupZoneNormalizer::toImageSpace($mockup, $mockup->map)
            : null;
        $flatArtworkUrl = $template->thumbnailArtworkUrl()
            ?: $template->previewArtworkUrl()
            ?: $template->baseArtworkUrl();

        return [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'base_image_url' => $template->base_template_url,
                'preview_image_url' => $flatArtworkUrl,
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
            'font_selection' => collect($fieldFonts)
                ->mapWithKeys(fn ($selectedFont, $fieldKey) => [$fieldKey => $selectedFont instanceof PersonalizationFont ? [
                    'id' => $selectedFont->id,
                    'name' => $selectedFont->name,
                    'css_font_family' => $selectedFont->css_font_family,
                ] : null])
                ->filter()
                ->all(),
            'flat' => [
                'image_url' => $flatArtworkUrl,
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
                    'top_left_x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                    'top_left_y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                    'top_right_x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                    'top_right_y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                    'bottom_right_x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                    'bottom_right_y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                    'bottom_left_x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                    'bottom_left_y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                    'opacity' => (float) ($mockup->map->opacity ?? 0.95),
                    'shadow_strength' => (float) ($mockup->map->shadow_strength ?? 0.18),
                    'highlight_strength' => (float) ($mockup->map->highlight_strength ?? 0.12),
                    'fit_mode' => $mockup->map->fit_mode ?? 'stretch',
                    'object_position_x' => (float) ($mockup->map->object_position_x ?? 0.5),
                    'object_position_y' => (float) ($mockup->map->object_position_y ?? 0.5),
                    'manual_rotation' => (float) ($mockup->map->manual_rotation ?? 0),
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

        $fieldFonts = collect($meta['font_selection'] ?? [])
            ->mapWithKeys(function ($selection, $fieldKey) use ($template, $product) {
                $fontName = data_get($selection, 'name');
                $fontId = data_get($selection, 'id');
                $resolvedFont = ($fontId ? $template?->fonts?->firstWhere('id', $fontId) : null)
                    ?? ($fontId ? $product->personalizationTemplate?->fonts?->firstWhere('id', $fontId) : null)
                    ?? ($fontName ? $template?->fonts?->firstWhere('name', $fontName) : null)
                    ?? ($fontName ? $product->personalizationTemplate?->fonts?->firstWhere('name', $fontName) : null);

                return [$fieldKey => $resolvedFont];
            })
            ->filter()
            ->all();

        return self::buildForProduct(
            $product,
            $meta['personalization'] ?? [],
            $font,
            $fieldFonts,
            $template,
            $mockup,
        );
    }
}
