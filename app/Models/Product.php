<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'type',
        'status',
        'excerpt',
        'description',
        'price',
        'compare_at_price',
        'lead_time_days',
        'manage_stock',
        'stock_quantity',
        'low_stock_threshold',
        'is_featured',
        'featured_image_url',
        'gallery_default_source',
        'show_flat_preview_first',
        'include_mockup_gallery',
        'live_preview_enabled',
        'video_url',
        'proof_notes_enabled',
        'font_presets_enabled',
        'personalization_help_text',
        'personalization_fields_blueprint',
        'variant_media_links',
        'shipping_care_policy',
        'product_faqs',
        'combo_discount_type',
        'combo_discount_value',
        'combo_rounding_rule',
        'show_combo_savings_badge',
        'combo_promo_headline',
        'combo_promo_subtitle',
        'marketing_label',
        'show_related_combos_on_product',
        'show_related_combos_in_cart',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'manage_stock' => 'boolean',
            'is_featured' => 'boolean',
            'show_flat_preview_first' => 'boolean',
            'include_mockup_gallery' => 'boolean',
            'live_preview_enabled' => 'boolean',
            'proof_notes_enabled' => 'boolean',
            'font_presets_enabled' => 'boolean',
            'personalization_fields_blueprint' => 'array',
            'variant_media_links' => 'array',
            'shipping_care_policy' => 'array',
            'product_faqs' => 'array',
            'combo_discount_value' => 'decimal:2',
            'show_combo_savings_badge' => 'boolean',
            'show_related_combos_on_product' => 'boolean',
            'show_related_combos_in_cart' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_related', 'product_id', 'related_product_id')->withTimestamps();
    }

    public function relatedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_related_categories')->withTimestamps();
    }

    public function bundleItems(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'bundle_product_id')->orderBy('position');
    }

    public function includedInBundles(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'child_product_id');
    }

    public function serviceMeta(): HasOne
    {
        return $this->hasOne(ServiceProductMeta::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function personalizationTemplate(): HasOne
    {
        return $this->hasOne(PersonalizationTemplate::class);
    }

    public function personalizationMockups(): BelongsToMany
    {
        return $this->belongsToMany(PersonalizationMockup::class, 'product_personalization_mockup')
            ->withPivot(['sort_order', 'is_default'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function getIsCustomizableAttribute(): bool
    {
        return $this->type === ProductType::AdvancedPersonalized;
    }

    public function primaryImage()
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true) ?: $this->images->first();
        }

        return $this->images()->orderByDesc('is_primary')->orderBy('position')->first();
    }

    public function defaultPersonalizationMockup(): ?PersonalizationMockup
    {
        if ($this->relationLoaded('personalizationMockups')) {
            $activeMockups = $this->personalizationMockups->where('is_active', true);

            return $activeMockups->firstWhere('pivot.is_default', true) ?: $activeMockups->first();
        }

        return $this->personalizationMockups()
            ->where('is_active', true)
            ->orderByDesc('product_personalization_mockup.is_default')
            ->orderBy('product_personalization_mockup.sort_order')
            ->first();
    }

    public function storefrontPreviewVersion(): string
    {
        $versionParts = [
            'preview-art-v5',
            (string) optional($this->updated_at)->timestamp,
        ];

        $template = $this->relationLoaded('personalizationTemplate')
            ? $this->personalizationTemplate
            : $this->personalizationTemplate()->first(['id', 'updated_at']);

        if ($template) {
            $versionParts[] = (string) optional($template->updated_at)->timestamp;
        }

        $defaultMockup = $this->defaultPersonalizationMockup();

        if ($defaultMockup) {
            $versionParts[] = (string) optional($defaultMockup->updated_at)->timestamp;

            $mockupMap = $defaultMockup->relationLoaded('map')
                ? $defaultMockup->map
                : $defaultMockup->map()->first(['id', 'updated_at']);

            if ($mockupMap) {
                $versionParts[] = (string) optional($mockupMap->updated_at)->timestamp;
            }
        }

        return substr(sha1(implode('|', array_filter($versionParts, fn ($part) => $part !== ''))), 0, 16);
    }

    public function getStorefrontPreviewImageUrlAttribute(): ?string
    {
        if ($this->is_customizable) {
            $template = $this->relationLoaded('personalizationTemplate')
                ? $this->personalizationTemplate
                : $this->personalizationTemplate()->first();

            if ($template) {
                $snapshotUrl = $template->storefrontArtworkUrl()
                    ?: $template->thumbnailArtworkUrl();
                $defaultMockup = $this->defaultPersonalizationMockup()
                    ?: $template->mockups()->where('is_active', true)->orderBy('sort_order')->first();

                if ($defaultMockup) {
                    return route('products.preview.image', [
                        'product' => $this,
                        'v' => $this->storefrontPreviewVersion(),
                    ]);
                }

                return $snapshotUrl ?: route('products.preview.image', [
                    'product' => $this,
                    'v' => $this->storefrontPreviewVersion(),
                ]);
            }

            return null;
        }

        return $this->featured_image_url ?: $this->primaryImage()?->image_url;
    }
}
