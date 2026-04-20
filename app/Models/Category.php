<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'storefront_excerpt',
        'image_url',
        'banner_image_url',
        'mobile_banner_image_url',
        'icon_image_url',
        'alt_text',
        'is_active',
        'sort_order',
        'is_featured',
        'show_on_homepage',
        'seo_image_url',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function relatedCategories(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'category_related', 'category_id', 'related_category_id')->withTimestamps();
    }
}
