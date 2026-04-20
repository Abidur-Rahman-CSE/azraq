<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizationMockupMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalization_mockup_id',
        'map_type',
        'fit_mode',
        'top_left_x',
        'top_left_y',
        'top_right_x',
        'top_right_y',
        'bottom_right_x',
        'bottom_right_y',
        'bottom_left_x',
        'bottom_left_y',
        'normalized_coordinates',
        'object_position_x',
        'object_position_y',
        'manual_rotation',
        'shadow_strength',
        'highlight_strength',
        'opacity',
    ];

    protected function casts(): array
    {
        return [
            'top_left_x' => 'decimal:4',
            'top_left_y' => 'decimal:4',
            'top_right_x' => 'decimal:4',
            'top_right_y' => 'decimal:4',
            'bottom_right_x' => 'decimal:4',
            'bottom_right_y' => 'decimal:4',
            'bottom_left_x' => 'decimal:4',
            'bottom_left_y' => 'decimal:4',
            'normalized_coordinates' => 'boolean',
            'object_position_x' => 'decimal:4',
            'object_position_y' => 'decimal:4',
            'manual_rotation' => 'decimal:2',
            'shadow_strength' => 'decimal:2',
            'highlight_strength' => 'decimal:2',
            'opacity' => 'decimal:2',
        ];
    }

    public function mockup(): BelongsTo
    {
        return $this->belongsTo(PersonalizationMockup::class, 'personalization_mockup_id');
    }
}
