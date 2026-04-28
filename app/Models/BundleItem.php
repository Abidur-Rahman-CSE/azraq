<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_product_id',
        'child_product_id',
        'quantity',
        'is_required',
        'default_variant_id',
        'allowed_variant_ids',
        'variant_change_allowed',
        'discount_eligible',
        'excluded_upgrade',
        'price_mode',
        'custom_price',
        'display_label',
        'show_on_hero',
        'show_in_details',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'allowed_variant_ids' => 'array',
            'variant_change_allowed' => 'boolean',
            'discount_eligible' => 'boolean',
            'excluded_upgrade' => 'boolean',
            'is_required' => 'boolean',
            'show_on_hero' => 'boolean',
            'show_in_details' => 'boolean',
            'custom_price' => 'decimal:2',
        ];
    }

    public function bundleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function childProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'child_product_id');
    }

    public function defaultVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'default_variant_id');
    }
}
