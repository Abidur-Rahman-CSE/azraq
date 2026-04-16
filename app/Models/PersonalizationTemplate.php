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
        'preview_image_url',
        'preview_rules',
        'render_rules',
        'instructions',
        'proof_note_label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'preview_rules' => 'array',
            'render_rules' => 'array',
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
}
