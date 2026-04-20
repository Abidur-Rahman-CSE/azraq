<?php

namespace App\Support;

use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonalizationAssetUsage
{
    public static function deleteManagedAssetIfUnused(?string $url): void
    {
        if (! filled($url)) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/storage/')) {
            return;
        }

        if (self::referenceCount($url) > 1) {
            return;
        }

        Storage::disk('public')->delete(Str::after($path, '/storage/'));
    }

    public static function referenceCount(string $url): int
    {
        return PersonalizationTemplate::query()->where('base_template_url', $url)->count()
            + PersonalizationTemplate::query()->where('preview_image_url', $url)->count()
            + PersonalizationTemplate::query()->where('mask_image_url', $url)->count()
            + PersonalizationMockup::query()->where('base_image_url', $url)->count()
            + PersonalizationMockup::query()->where('overlay_image_url', $url)->count()
            + PersonalizationMockup::query()->where('mask_image_url', $url)->count()
            + PersonalizationMockup::query()->where('thumb_image_url', $url)->count();
    }
}
