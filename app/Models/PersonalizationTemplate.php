<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalizationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'base_template_url',
        'preview_image_url',
        'mask_image_url',
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
        return $this->hasMany(PersonalizationField::class)->orderBy('position');
    }

    public function fonts(): HasMany
    {
        return $this->hasMany(PersonalizationFont::class)->orderBy('position');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(PersonalizationMockup::class)->orderBy('sort_order');
    }
}
