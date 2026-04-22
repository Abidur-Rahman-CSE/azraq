<?php

namespace App\Support;

use App\Models\PersonalizationMockup;
use App\Models\PersonalizationMockupMap;

class MockupZoneNormalizer
{
    private const STAGE_RATIO = 4 / 3;

    public static function toImageSpace(?PersonalizationMockup $mockup, ?PersonalizationMockupMap $map): ?array
    {
        if (! $mockup || ! $map) {
            return null;
        }

        $points = [
            'top_left_x' => (float) $map->top_left_x,
            'top_left_y' => (float) $map->top_left_y,
            'top_right_x' => (float) $map->top_right_x,
            'top_right_y' => (float) $map->top_right_y,
            'bottom_right_x' => (float) $map->bottom_right_x,
            'bottom_right_y' => (float) $map->bottom_right_y,
            'bottom_left_x' => (float) $map->bottom_left_x,
            'bottom_left_y' => (float) $map->bottom_left_y,
        ];

        if (($map->coordinate_space ?? 'stage') === 'image') {
            return $points;
        }

        [$imageWidth, $imageHeight] = self::resolveImageDimensions($mockup);

        if (! $imageWidth || ! $imageHeight) {
            return $points;
        }

        $imageRatio = $imageWidth / max(1, $imageHeight);
        $widthNorm = $imageRatio > self::STAGE_RATIO ? 1.0 : ($imageRatio / self::STAGE_RATIO);
        $heightNorm = $imageRatio > self::STAGE_RATIO ? (self::STAGE_RATIO / $imageRatio) : 1.0;
        $left = (1 - $widthNorm) / 2;
        $top = (1 - $heightNorm) / 2;

        return [
            'top_left_x' => self::normalize($points['top_left_x'], $left, $widthNorm),
            'top_left_y' => self::normalize($points['top_left_y'], $top, $heightNorm),
            'top_right_x' => self::normalize($points['top_right_x'], $left, $widthNorm),
            'top_right_y' => self::normalize($points['top_right_y'], $top, $heightNorm),
            'bottom_right_x' => self::normalize($points['bottom_right_x'], $left, $widthNorm),
            'bottom_right_y' => self::normalize($points['bottom_right_y'], $top, $heightNorm),
            'bottom_left_x' => self::normalize($points['bottom_left_x'], $left, $widthNorm),
            'bottom_left_y' => self::normalize($points['bottom_left_y'], $top, $heightNorm),
        ];
    }

    public static function resolveImageDimensions(PersonalizationMockup $mockup): array
    {
        if ($mockup->image_width && $mockup->image_height) {
            return [(int) $mockup->image_width, (int) $mockup->image_height];
        }

        $path = parse_url((string) $mockup->base_image_url, PHP_URL_PATH);

        if (! $path) {
            return [null, null];
        }

        $storageRelativePath = ltrim(str_replace('/storage/', '', $path), '/');
        $candidates = [
            storage_path('app/public/'.$storageRelativePath),
            public_path(ltrim($path, '/')),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $dimensions = @getimagesize($candidate);

                if (is_array($dimensions)) {
                    return [(int) $dimensions[0], (int) $dimensions[1]];
                }
            }
        }

        return [null, null];
    }

    private static function normalize(float $value, float $offset, float $span): float
    {
        if ($span <= 0) {
            return $value;
        }

        return max(0, min(1, ($value - $offset) / $span));
    }
}
