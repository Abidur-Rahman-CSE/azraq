<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProductMeta extends Model
{
    use HasFactory;

    protected $table = 'service_product_meta';

    protected $fillable = [
        'product_id',
        'service_type',
        'duration_label',
        'location_scope',
        'requires_advance_payment',
        'advance_payment_amount',
        'booking_notes',
    ];

    protected function casts(): array
    {
        return [
            'requires_advance_payment' => 'boolean',
            'advance_payment_amount' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
