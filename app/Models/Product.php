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
}
