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

        $homepageSection->update($request->safe()->except([
            'desktop_image_upload',
            'mobile_image_upload',
            'settings',
        ]) + [
            'is_enabled' => $request->boolean('is_enabled'),
            'sort_order' => $request->integer('sort_order'),
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
