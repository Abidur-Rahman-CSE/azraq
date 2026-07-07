<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonalizationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'base_template_url',
        'preview_image_url',
        'mask_image_url',
        'thumbnail_image_url',
        'export_ratio_width',
        'export_ratio_height',
        'preview_rules',
        'render_rules',
        'preview_data_presets',
        'instructions',
        'safe_zone_notes',
        'proof_note_label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'preview_rules' => 'array',
            'render_rules' => 'array',
            'preview_data_presets' => 'array',
            'export_ratio_width' => 'integer',
            'export_ratio_height' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(PersonalizationField::class)->orderBy('position', 'asc');
    }

    public function fonts(): HasMany
    {
        return $this->hasMany(PersonalizationFont::class)->orderBy('position', 'asc');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(PersonalizationMockup::class)->orderBy('sort_order', 'asc');
    }

    public static function imageUrlExists(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '' || str_starts_with($url, 'blob:')) {
            return false;
        }

        if (str_starts_with($url, 'data:')) {
            return true;
        }

        if (preg_match('/^https?:\/\//i', $url)) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        if (! preg_match('/\.(avif|gif|jpe?g|png|svg|webp)$/i', $path)) {
            return true;
        }

        if (str_starts_with($path, '/storage/')) {
            return Storage::disk('public')->exists(Str::after($path, '/storage/'));
        }

        return file_exists(public_path(ltrim($path, '/')));
    }

    public function firstExistingImageUrl(?string ...$urls): ?string
    {
        foreach ($urls as $url) {
            if (self::imageUrlExists($url)) {
                return $url;
            }
        }

        return null;
    }

    public function baseArtworkUrl(): ?string
    {
        return $this->firstExistingImageUrl(
            $this->base_template_url,
            $this->preview_image_url,
            $this->thumbnail_image_url,
        );
    }

    public function previewArtworkUrl(): ?string
    {
        return $this->firstExistingImageUrl(
            $this->preview_image_url,
            $this->base_template_url,
            $this->thumbnail_image_url,
        );
    }

    public function thumbnailArtworkUrl(): ?string
    {
        return $this->firstExistingImageUrl(
            $this->thumbnail_image_url,
            $this->preview_image_url,
            $this->base_template_url,
        );
    }

    public function storefrontArtworkUrl(): ?string
    {
        return $this->firstExistingImageUrl(
            $this->preview_image_url,
            $this->base_template_url,
            $this->thumbnail_image_url,
        );
    }

    public function storefrontPreviewArtworkUrl(): ?string
    {
        return $this->firstExistingImageUrl(
            $this->preview_image_url,
            $this->base_template_url,
            $this->thumbnail_image_url,
        );
    }
}
