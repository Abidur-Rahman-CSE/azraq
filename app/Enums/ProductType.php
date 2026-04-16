<?php

namespace App\Enums;

enum ProductType: string
{
    case Standard = 'standard';
    case LightCustomizable = 'light_customizable';
    case AdvancedPersonalized = 'advanced_personalized';
    case Bundle = 'bundle';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Product',
            self::LightCustomizable => 'Light Customizable',
            self::AdvancedPersonalized => 'Advanced Personalized',
            self::Bundle => 'Bundle / Combo',
            self::Service => 'Service / Booking',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases(),
        );
    }
}
