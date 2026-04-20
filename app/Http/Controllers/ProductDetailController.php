<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function show(Request $request, Product $product)
    {
        $product->load([
            'category',
            'tags',
            'images',
            'variants',
            'bundleItems.childProduct.category',
            'bundleItems.childProduct.tags',
            'bundleItems.childProduct.images',
            'serviceMeta',
            'personalizationTemplate.fields',
            'personalizationTemplate.fonts',
            'personalizationMockups.map',
            'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(4),
            'relatedProducts.category',
            'relatedProducts.tags',
            'relatedProducts.images',
            'relatedCategories',
        ]);

        if ($product->type === ProductType::AdvancedPersonalized && $product->personalizationTemplate) {
            $template = $product->personalizationTemplate;
            $mockups = $product->personalizationMockups
                ->where('is_active', true)
                ->values();

            $flatGalleryItem = [
                'id' => 'template-flat',
                'kind' => 'flat',
                'title' => 'Flat certificate preview',
                'eyebrow' => 'Template view',
                'thumb' => $template->preview_image_url ?: $template->base_template_url,
                'scene' => $template->preview_image_url ?: $template->base_template_url,
            ];

            $mockupGalleryItems = $mockups->map(function ($mockup) {
                $map = $mockup->map;

                return [
                    'id' => 'mockup-'.$mockup->id,
                    'kind' => 'mockup',
                    'title' => $mockup->title,
                    'eyebrow' => 'Scene mockup',
                    'thumb' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                    'scene' => $mockup->base_image_url,
                    'overlay' => $mockup->overlay_image_url,
                    'mask' => $mockup->mask_image_url,
                    'map' => [
                        'top_left_x' => (float) ($map?->top_left_x ?? 0.2),
                        'top_left_y' => (float) ($map?->top_left_y ?? 0.18),
                        'top_right_x' => (float) ($map?->top_right_x ?? 0.8),
                        'top_right_y' => (float) ($map?->top_right_y ?? 0.18),
                        'bottom_right_x' => (float) ($map?->bottom_right_x ?? 0.8),
                        'bottom_right_y' => (float) ($map?->bottom_right_y ?? 0.82),
                        'bottom_left_x' => (float) ($map?->bottom_left_x ?? 0.2),
                        'bottom_left_y' => (float) ($map?->bottom_left_y ?? 0.82),
                        'opacity' => (float) ($map?->opacity ?? 0.95),
                        'shadow_strength' => (float) ($map?->shadow_strength ?? 0.18),
                        'highlight_strength' => (float) ($map?->highlight_strength ?? 0.12),
                    ],
                ];
            })->values();

            $galleryItems = collect();

            if ($product->show_flat_preview_first || ! $product->include_mockup_gallery || $mockupGalleryItems->isEmpty()) {
                $galleryItems->push($flatGalleryItem);
            }

            if ($product->include_mockup_gallery) {
                $galleryItems = $galleryItems->concat($mockupGalleryItems);
            }

            if (! $product->show_flat_preview_first && $galleryItems->where('kind', 'flat')->isEmpty()) {
                $galleryItems->push($flatGalleryItem);
            }

            return view('storefront.products.personalized', [
                'product' => $product,
                'template' => $template,
                'galleryItems' => $galleryItems->values(),
            ]);
        }

        if ($product->type === ProductType::Service && $product->serviceMeta) {
            return view('storefront.products.service', [
                'product' => $product,
                'serviceMeta' => $product->serviceMeta,
            ]);
        }

        if (! in_array($product->type, [ProductType::Standard, ProductType::LightCustomizable, ProductType::Bundle], true)) {
            return redirect()
                ->route('shop.index', ['type' => $product->type?->value])
                ->with('status', 'This product type will get its dedicated detail flow in a later phase.');
        }

        $recentlyViewed = collect($request->session()->get('recently_viewed_products', []))
            ->reject(fn (int $id) => $id === $product->id)
            ->take(4)
            ->whenNotEmpty(fn ($ids) => Product::with(['category', 'tags', 'images'])->whereIn('id', $ids)->get()->sortBy(fn ($item) => array_search($item->id, $ids->all())))
            ->values();

        $request->session()->put('recently_viewed_products', collect([$product->id])
            ->merge($request->session()->get('recently_viewed_products', []))
            ->unique()
            ->take(8)
            ->values()
            ->all());

        return match ($product->type) {
            ProductType::LightCustomizable => view('storefront.products.light-customizable', [
                'product' => $product,
                'recentlyViewed' => $recentlyViewed,
            ]),
            ProductType::Bundle => view('storefront.products.bundle', [
                'product' => $product,
                'recentlyViewed' => $recentlyViewed,
                'bundleValue' => $product->bundleItems->sum(fn ($item) => (float) $item->childProduct?->price * $item->quantity),
            ]),
            default => view('storefront.products.show', [
                'product' => $product,
                'recentlyViewed' => $recentlyViewed,
            ]),
        };
    }
}
