<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PersonalizationMockup extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalization_template_id',
        'title',
        'slug',
        'base_image_url',
        'overlay_image_url',
        'mask_image_url',
        'thumb_image_url',
        'render_mode',
        'sort_order',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PersonalizationTemplate::class, 'personalization_template_id');
    }

    public function map(): HasOne
    {
        return $this->hasOne(PersonalizationMockupMap::class);
    }
}
