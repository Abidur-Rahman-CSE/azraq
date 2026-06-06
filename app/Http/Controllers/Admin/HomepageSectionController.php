<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSectionRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();

        return view('admin.content.homepage-sections.index', compact('sections'));
    }

    public function edit(HomepageSection $homepageSection)
    {
        $settings = $homepageSection->settings ?? [];

        return view('admin.content.homepage-sections.edit', [
            'section' => $homepageSection,
            'settings' => $settings,
            'collections' => Collection::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'cover_image_url']),
            'products' => Product::query()->with(['images', 'personalizationTemplate', 'personalizationMockups'])->orderBy('name')->get(['id', 'name', 'slug', 'featured_image_url', 'updated_at']),
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug', 'image_url', 'banner_image_url', 'alt_text']),
        ]);
    }

    public function update(HomepageSectionRequest $request, HomepageSection $homepageSection)
    {
        $settings = $homepageSection->settings ?? [];
        $incomingSettings = (array) $request->input('settings', []);

        if ($homepageSection->section_key === 'hero') {
            $settings['desktop_image_url'] = $this->resolveUpload(
                $request->file('desktop_image_upload'),
                $incomingSettings['desktop_image_url'] ?? ($settings['desktop_image_url'] ?? null)
            );
            $settings['mobile_image_url'] = $this->resolveUpload(
                $request->file('mobile_image_upload'),
                $incomingSettings['mobile_image_url'] ?? ($settings['mobile_image_url'] ?? null)
            );
            $settings['secondary_cta_label'] = $incomingSettings['secondary_cta_label'] ?? null;
            $settings['secondary_cta_href'] = $incomingSettings['secondary_cta_href'] ?? null;
            $settings['featured_product_id'] = isset($incomingSettings['featured_product_id']) && $incomingSettings['featured_product_id'] !== ''
                ? (int) $incomingSettings['featured_product_id']
                : null;

            // Carousel slides
            $slideFiles = $request->file('slide_images', []);
            $rawSlides = (array) ($incomingSettings['slides'] ?? []);
            $deleteSlides = (array) $request->input('_delete_slide', []);
            $slides = [];
            foreach ($rawSlides as $idx => $slide) {
                // Skip slides marked for deletion
                if (!empty($deleteSlides[$idx])) continue;
                $title = trim((string) ($slide['title'] ?? ''));
                $imageUrl = $this->resolveUpload(
                    $slideFiles[$idx] ?? null,
                    $slide['image_url'] ?? null
                );
                // Skip completely empty rows
                if ($title === '' && $imageUrl === null) continue;
                $slides[] = [
                    'image_url'  => $imageUrl,
                    'title'      => $title,
                    'subtitle'   => trim((string) ($slide['subtitle'] ?? '')),
                    'body'       => trim((string) ($slide['body'] ?? '')),
                    'cta_label'  => trim((string) ($slide['cta_label'] ?? '')),
                    'cta_href'   => trim((string) ($slide['cta_href'] ?? '')),
                    'cta2_label' => trim((string) ($slide['cta2_label'] ?? '')),
                    'cta2_href'  => trim((string) ($slide['cta2_href'] ?? '')),
                ];
            }
            $settings['slides'] = array_values($slides);
        }

        if ($homepageSection->section_key === 'featured_collections') {
            $settings['selected_collection_ids'] = array_values(array_map('intval', $incomingSettings['selected_collection_ids'] ?? []));
        }

        if ($homepageSection->section_key === 'featured_products') {
            $settings['selected_product_ids'] = array_values(array_map('intval', $incomingSettings['selected_product_ids'] ?? []));
        }

        if ($homepageSection->section_key === 'featured_categories') {
            $settings['selected_category_ids'] = array_values(array_map('intval', $incomingSettings['selected_category_ids'] ?? []));
        }

        if ($homepageSection->section_key === 'stats_strip') {
            $rows = (array) ($incomingSettings['stats'] ?? []);
            $settings['stats'] = array_values(array_filter(array_map(function ($row) {
                $num = trim((string) ($row['num'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
                return ($num === '' && $label === '') ? null : ['num' => $num, 'label' => $label];
            }, $rows)));
        }

        if ($homepageSection->section_key === 'signature_nikah_spotlight') {
            $settings['image_url'] = $this->resolveUpload(
                $request->file('spotlight_image_upload'),
                $incomingSettings['image_url'] ?? ($settings['image_url'] ?? null)
            );
            $settings['product_id'] = isset($incomingSettings['product_id']) && $incomingSettings['product_id'] !== ''
                ? (int) $incomingSettings['product_id']
                : null;
            $steps = (array) ($incomingSettings['process_steps'] ?? []);
            $settings['process_steps'] = array_values(array_filter(array_map(
                fn ($s) => trim((string) $s),
                $steps
            ), fn ($s) => $s !== ''));
            $settings['secondary_cta_label'] = $incomingSettings['secondary_cta_label'] ?? null;
            $settings['secondary_cta_href'] = $incomingSettings['secondary_cta_href'] ?? null;
        }

        if ($homepageSection->section_key === 'atelier_services') {
            $settings['service_ids'] = array_values(array_map('intval', $incomingSettings['service_ids'] ?? []));
        }

        if ($homepageSection->section_key === 'finale_cta') {
            $settings['background_image_url'] = $this->resolveUpload(
                $request->file('background_image_upload'),
                $incomingSettings['background_image_url'] ?? ($settings['background_image_url'] ?? null)
            );
            $settings['secondary_cta_label'] = $incomingSettings['secondary_cta_label'] ?? null;
            $settings['secondary_cta_href'] = $incomingSettings['secondary_cta_href'] ?? null;
        }

        if ($homepageSection->section_key === 'instagram_strip') {
            $rows = (array) ($incomingSettings['posts'] ?? []);
            $settings['posts'] = array_values(array_filter(array_map(function ($row) {
                $image = trim((string) ($row['image_url'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));
                return $image === '' ? null : ['image_url' => $image, 'href' => $href];
            }, $rows)));
        }

        if ($homepageSection->section_key === 'trust_strip') {
            $rows = (array) ($incomingSettings['signals'] ?? []);
            $settings['signals'] = array_values(array_filter(array_map(function ($row) {
                $icon = trim((string) ($row['icon'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
                return $label === '' ? null : ['icon' => $icon ?: '◆', 'label' => $label];
            }, $rows)));
        }

        $titleFallback = filled($request->input('title')) ? $request->input('title') : $homepageSection->title;

        $homepageSection->update($request->safe()->except([
            'desktop_image_upload',
            'mobile_image_upload',
            'background_image_upload',
            'spotlight_image_upload',
            'slide_images',
            'settings',
        ]) + [
            'title' => $titleFallback,
            'is_enabled' => $request->boolean('is_enabled'),
            'sort_order' => (int) ($request->input('sort_order') ?? $homepageSection->sort_order),
            'settings' => $settings,
        ]);

        foreach ([
            'storefront.home.section_keys',
            'storefront.home.featured_product_ids',
            'storefront.home.featured_category_ids',
            'storefront.home.featured_collection_ids',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }

        return redirect()->route('admin.content.homepage-sections.index')->with('status', 'Homepage section updated.');
    }

    private function resolveUpload(?UploadedFile $file, ?string $current): ?string
    {
        if (! $file instanceof UploadedFile) {
            return $current;
        }

        $path = $file->store('homepage-sections', 'public');

        return Storage::url($path);
    }
}
