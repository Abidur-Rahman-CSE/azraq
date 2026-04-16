<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizationFont extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalization_template_id',
        'name',
        'css_font_family',
        'preview_label',
        'position',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PersonalizationTemplate::class, 'personalization_template_id');
    }
}
