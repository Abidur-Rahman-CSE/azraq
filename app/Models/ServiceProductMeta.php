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
        'confirmation_note',
        'available_areas',
        'available_days',
        'time_slot_options',
        'minimum_notice_days',
        'max_bookings_per_day',
        'travel_outside_area_allowed',
        'extra_charge_note',
        'include_items',
        'packages',
        'booking_flow',
        'before_appointment',
        'pricing_notes',
        'policies',
        'faqs',
        'gallery_intro_text',
    ];

    protected function casts(): array
    {
        return [
            'requires_advance_payment' => 'boolean',
            'advance_payment_amount' => 'decimal:2',
            'minimum_notice_days' => 'integer',
            'max_bookings_per_day' => 'integer',
            'travel_outside_area_allowed' => 'boolean',
            'include_items' => 'array',
            'packages' => 'array',
            'booking_flow' => 'array',
            'before_appointment' => 'array',
            'pricing_notes' => 'array',
            'policies' => 'array',
            'faqs' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
