<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizationFont extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalization_template_id',
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
        'position',
        'sort_order',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'letter_spacing_default' => 'float',
            'line_height_default' => 'float',
        ];
    }

    public function getResolvedFontFamilyAttribute(): string
    {
        return $this->font_family ?: $this->css_font_family;
    }

    public static function starterPresets(): array
    {
        $hardcoded = [
            self::starterPreset('Classic Script', 'classic_script', 'Classic Script', '"Great Vibes", cursive', 'google', 'https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap', 'Classic Script', 'Classic Script', 'all', 'Amena & Hassan', '600', 'normal', 0.2, 1.25, 'none', 'bride_name,groom_name', true, true, 0),
            self::starterPreset('Royal Script', 'royal_script', 'Royal Script', '"Allura", cursive', 'google', 'https://fonts.googleapis.com/css2?family=Allura&display=swap', 'Signature Script', 'Luxury Calligraphy', 'all', 'Amena & Hassan', '600', 'normal', 0.15, 1.25, 'none', 'bride_name,groom_name', true, false, 10),
            self::starterPreset('Elegant Signature', 'elegant_signature', 'Elegant Signature', '"Alex Brush", cursive', 'google', 'https://fonts.googleapis.com/css2?family=Alex+Brush&display=swap', 'Signature Script', 'Luxury Calligraphy', 'all', 'Amena', '600', 'normal', 0.1, 1.2, 'none', 'bride_name', true, false, 20),
            self::starterPreset('Timeless Serif', 'timeless_serif', 'Timeless Serif', '"Cormorant Garamond", serif', 'google', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap', 'Elegant Serif', 'Elegant Serif', 'all', 'Ceremony Date', '600', 'normal', 0.4, 1.2, 'uppercase', 'all', true, false, 30),
            self::starterPreset('Modern Serif', 'modern_serif', 'Modern Serif', '"Playfair Display", serif', 'google', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap', 'Modern Serif', 'Modern Serif', 'all', 'Dhaka', '600', 'normal', 0.35, 1.2, 'uppercase', 'date,venue', true, false, 40),
            self::starterPreset('Premium Sans', 'premium_sans', 'Premium Sans', '"Poppins", sans-serif', 'google', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', 'Minimal Sans', 'Minimal Sans', 'all', 'Amena & Hassan', '600', 'normal', 0, 1.15, 'none', 'all', true, false, 50),
            self::starterPreset('Formal Roman', 'formal_roman', 'Formal Roman', '"Cinzel", serif', 'google', 'https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&display=swap', 'Formal Roman', 'Formal Roman', 'all', 'Nikah Nama', '600', 'normal', 0.8, 1.2, 'uppercase', 'date,venue,all', true, false, 60),
            self::starterPreset('Minimal Elegant', 'minimal_elegant', 'Minimal Elegant', '"Marcellus", serif', 'google', 'https://fonts.googleapis.com/css2?family=Marcellus&display=swap', 'Modern Serif', 'Minimal Sans', 'all', 'Amena', '500', 'normal', 0.2, 1.2, 'none', 'all', true, false, 70),
            self::starterPreset('Luxury Calligraphy', 'luxury_calligraphy', 'Luxury Calligraphy', '"Parisienne", cursive', 'google', 'https://fonts.googleapis.com/css2?family=Parisienne&display=swap', 'Luxury Calligraphy', 'Luxury Calligraphy', 'all', 'Hassan', '600', 'normal', 0.15, 1.25, 'none', 'groom_name', true, false, 80),
            self::starterPreset('Soft Serif', 'soft_serif', 'Soft Serif', '"Lora", serif', 'google', 'https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&display=swap', 'Elegant Serif', 'Elegant Serif', 'all', 'Dhaka', '500', 'normal', 0.2, 1.25, 'none', 'venue,all', true, false, 90),
        ];

        // Merge user-added starters persisted in settings table
        try {
            $extra = \Illuminate\Support\Facades\DB::table('settings')
                ->where('key', 'personalization_font_starters')
                ->value('value');

            if ($extra) {
                $decoded = json_decode($extra, true);
                if (is_array($decoded)) {
                    $hardcoded_keys = array_column($hardcoded, 'internal_name');
                    foreach ($decoded as $custom) {
                        if (!in_array($custom['internal_name'] ?? '', $hardcoded_keys, true)) {
                            $hardcoded[] = $custom;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Table may not exist during migrations — skip gracefully
        }

        return $hardcoded;
    }

    protected static function starterPreset(
        string $name,
        string $internalName,
        string $previewLabel,
        string $fontFamily,
        string $sourceType,
        string $sourceValue,
        string $category,
        string $styleType,
        string $supportedUse,
        string $previewSampleText,
        string $fontWeight,
        string $fontStyle,
        float $letterSpacing,
        float $lineHeight,
        string $textTransform,
        string $recommendedFor,
        bool $isActive,
        bool $isDefault,
        int $sortOrder,
    ): array {
        return [
            'name' => $name,
            'internal_name' => $internalName,
            'preview_label' => $previewLabel,
            'css_font_family' => $fontFamily,
            'font_family' => $fontFamily,
            'font_source_type' => $sourceType,
            'font_source_value' => $sourceValue,
            'category' => $category,
            'style_type' => $styleType,
            'supported_use' => $supportedUse,
            'preview_sample_text' => $previewSampleText,
            'font_weight_default' => $fontWeight,
            'font_style_default' => $fontStyle,
            'letter_spacing_default' => $letterSpacing,
            'line_height_default' => $lineHeight,
            'text_transform_default' => $textTransform,
            'recommended_for' => $recommendedFor,
            'is_active' => $isActive,
            'is_default' => $isDefault,
            'sort_order' => $sortOrder,
            'position' => $sortOrder,
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PersonalizationTemplate::class, 'personalization_template_id');
    }
}
