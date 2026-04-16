<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalization_template_id',
        'label',
        'field_key',
        'placeholder',
        'help_text',
        'default_value',
        'max_length',
        'min_length',
        'font_size_min',
        'font_size_max',
        'line_height',
        'letter_spacing',
        'text_align',
        'text_color',
        'position_x',
        'position_y',
        'width',
        'height',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'line_height' => 'decimal:2',
            'letter_spacing' => 'decimal:2',
            'position_x' => 'decimal:2',
            'position_y' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PersonalizationTemplate::class, 'personalization_template_id');
    }
}
