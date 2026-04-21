<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\PersonalizationFont;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartSession
{
    public static function items(Request $request): Collection
    {
        $cart = collect($request->session()->get('cart.items', []));

        $productIds = $cart->pluck('product_id')->filter()->all();
        $variantIds = $cart->pluck('variant_id')->filter()->all();
        $fontIds = $cart->pluck('font_id')->filter()->all();
        $mockupIds = $cart->pluck('mockup_id')->filter()->all();

        $products = Product::with(['category', 'images', 'bundleItems.childProduct.images'])->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
        $fonts = PersonalizationFont::whereIn('id', $fontIds)->get()->keyBy('id');
        $mockups = PersonalizationMockup::whereIn('id', $mockupIds)->get()->keyBy('id');

        return $cart->map(function (array $item) use ($products, $variants, $fonts, $mockups) {
            $product = $products->get($item['product_id']);
            $variant = $item['variant_id'] ? $variants->get($item['variant_id']) : null;
            $font = $item['font_id'] ? $fonts->get($item['font_id']) : null;
            $mockup = $item['mockup_id'] ? $mockups->get($item['mockup_id']) : null;
            $unitPrice = (float) ($variant?->price ?: $product?->price ?: 0);
            $quantity = (int) $item['quantity'];

            return [
                ...$item,
                'product' => $product,
                'variant' => $variant,
                'font' => $font,
                'mockup' => $mockup,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
            ];
        })->filter(fn (array $item) => $item['product'])->values();
    }

    public static function summary(Collection $items, string $shippingMethod = 'standard', ?Coupon $coupon = null): array
    {
        $subtotal = $items->sum('subtotal');
        $shipping = $items->isEmpty() ? 0 : match ($shippingMethod) {
            'express' => 240,
            default => 120,
        };
        $discount = 0;

        if ($coupon && $coupon->is_active && $subtotal >= (float) $coupon->minimum_order_amount) {
            $discount = $coupon->type === 'percent'
                ? round($subtotal * ((float) $coupon->value / 100), 2)
                : min($subtotal, (float) $coupon->value);
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => max(0, $subtotal + $shipping - $discount),
        ];
    }

    public static function availableStock(Product $product, ?ProductVariant $variant = null): ?int
    {
        if (! $product->manage_stock) {
            return null;
        }

        return (int) ($variant?->stock_quantity ?? $product->stock_quantity);
    }

    public static function hasSufficientStock(Product $product, ?ProductVariant $variant, int $requestedQuantity): bool
    {
        $available = self::availableStock($product, $variant);

        return $available === null || $requestedQuantity <= $available;
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget(['cart.items', 'cart.coupon_id']);
    }
}
