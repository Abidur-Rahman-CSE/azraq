<?php

use App\Models\PersonalizationMockup;
use App\Support\MockupZoneNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personalization_mockups') || ! Schema::hasTable('personalization_mockup_maps')) {
            return;
        }

        PersonalizationMockup::query()
            ->with('map')
            ->each(function (PersonalizationMockup $mockup): void {
                [$imageWidth, $imageHeight] = MockupZoneNormalizer::resolveImageDimensions($mockup);

                if (($imageWidth || $imageHeight) && (! $mockup->image_width || ! $mockup->image_height)) {
                    $mockup->forceFill([
                        'image_width' => $imageWidth ?: $mockup->image_width,
                        'image_height' => $imageHeight ?: $mockup->image_height,
                    ])->save();
                }

                if (! $mockup->map) {
                    return;
                }

                if (($mockup->map->coordinate_space ?? 'stage') === 'image') {
                    return;
                }

                $normalizedMap = MockupZoneNormalizer::toImageSpace($mockup, $mockup->map);

                if (! $normalizedMap) {
                    return;
                }

                $mockup->map->forceFill([
                    'top_left_x' => $normalizedMap['top_left_x'],
                    'top_left_y' => $normalizedMap['top_left_y'],
                    'top_right_x' => $normalizedMap['top_right_x'],
                    'top_right_y' => $normalizedMap['top_right_y'],
                    'bottom_right_x' => $normalizedMap['bottom_right_x'],
                    'bottom_right_y' => $normalizedMap['bottom_right_y'],
                    'bottom_left_x' => $normalizedMap['bottom_left_x'],
                    'bottom_left_y' => $normalizedMap['bottom_left_y'],
                    'coordinate_space' => 'image',
                    'normalized_coordinates' => true,
                ])->save();
            });
    }

    public function down(): void
    {
        // Not safely reversible once legacy stage-space coordinates are normalized.
    }
};
