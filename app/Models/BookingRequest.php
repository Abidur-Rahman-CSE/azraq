<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'booking_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'preferred_date',
        'preferred_time',
        'location_area',
        'package_details',
        'notes',
        'status',
        'deposit_required',
        'deposit_amount',
        'deposit_status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'deposit_required' => 'boolean',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
