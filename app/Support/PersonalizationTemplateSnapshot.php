<?php

namespace App\Support;

use App\Models\PersonalizationTemplate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonalizationTemplateSnapshot
{
    public static function regenerate(PersonalizationTemplate $template): ?string
    {
        $template->loadMissing(['fields', 'fonts']);

        $backgroundUrl = $template->base_template_url ?: $template->preview_image_url;

        if (! filled($backgroundUrl)) {
            return null;
        }

        $defaultFont = $template->fonts
            ->where('is_active', true)
            ->sortByDesc(fn ($font) => (int) $font->is_default)
            ->sortBy('sort_order')
            ->first();

        $svg = view('admin.personalization.templates.snapshot-svg', [
            'template' => $template,
            'backgroundHref' => self::inlineAsset($backgroundUrl) ?: $backgroundUrl,
            'fontFamily' => $defaultFont?->resolved_font_family ?: 'Poppins, sans-serif',
            'fields' => $template->fields->sortBy('position')->values(),
        ])->render();

        $currentUrl = $template->thumbnail_image_url;
        $path = 'personalization/templates/snapshots/template-'.$template->id.'-'.now()->format('YmdHisv').'.svg';

        Storage::disk('public')->put($path, $svg);

        $url = Storage::url($path);
        $template->forceFill(['thumbnail_image_url' => $url])->saveQuietly();

        PersonalizationAssetUsage::deleteManagedAssetIfUnused($currentUrl);

        return $url;
    }

    private static function inlineAsset(?string $url): ?string
    {
        if (! filled($url) || ! is_string($url)) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $absolutePath = match (true) {
            str_starts_with($path, '/storage/') => storage_path('app/public/'.Str::after($path, '/storage/')),
            str_starts_with($path, '/images/') => public_path(ltrim($path, '/')),
            default => null,
        };

        if (! $absolutePath || ! File::exists($absolutePath)) {
            return null;
        }

        $mime = File::mimeType($absolutePath) ?: 'image/png';
        $contents = File::get($absolutePath);

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
