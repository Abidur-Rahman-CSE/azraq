<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Faq;
use App\Models\Product;
use App\Support\MockupZoneNormalizer;
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

        $faqs = Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        if ($product->type === ProductType::AdvancedPersonalized && $product->personalizationTemplate) {
            $template = $product->personalizationTemplate;
            $mockups = $product->personalizationMockups
                ->where('is_active', true)
                ->values();

            return view('storefront.products.show', [
                'product' => $product,
                'template' => $template,
                'fonts' => $template->fonts
                    ->where('is_active', true)
                    ->sortBy('sort_order')
                    ->values(),
                'mockups' => $mockups->map(function ($mockup) {
                    $map = $mockup->map;
                    $normalizedMap = MockupZoneNormalizer::toImageSpace($mockup, $map);

                    return [
                        'id' => $mockup->id,
                        'name' => $mockup->title,
                        'thumbnail_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                        'image_url' => $mockup->base_image_url,
                        'overlay_url' => $mockup->overlay_image_url ?: $mockup->mask_image_url,
                        'zone_points' => [
                            'tl' => [
                                'x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                                'y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                            ],
                            'tr' => [
                                'x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                                'y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                            ],
                            'br' => [
                                'x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                                'y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                            ],
                            'bl' => [
                                'x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                                'y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                            ],
                        ],
                        'image_width' => (int) ($mockup->image_width ?: 1600),
                        'image_height' => (int) ($mockup->image_height ?: 1200),
                        'opacity' => (float) ($map?->opacity ?? 0.95),
                        'shadow_strength' => (float) ($map?->shadow_strength ?? 0.18),
                        'highlight_strength' => (float) ($map?->highlight_strength ?? 0.12),
                        'fit_mode' => $map?->fit_mode ?? 'stretch',
                        'manual_rotation' => (float) ($map?->manual_rotation ?? 0),
                    ];
                })->values(),
                'faqs' => $faqs,
                'related_products' => $product->relatedProducts->values(),
                'recentlyViewed' => $recentlyViewed,
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
                'template' => null,
                'fonts' => collect(),
                'mockups' => collect(),
                'faqs' => $faqs,
                'related_products' => $product->relatedProducts->values(),
                'recentlyViewed' => $recentlyViewed,
            ]),
        };
    }
}
