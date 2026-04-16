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
            'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(4),
            'relatedProducts.category',
            'relatedProducts.tags',
            'relatedProducts.images',
        ]);

        if ($product->type === ProductType::AdvancedPersonalized && $product->personalizationTemplate) {
            return view('storefront.products.personalized', [
                'product' => $product,
                'template' => $product->personalizationTemplate,
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
