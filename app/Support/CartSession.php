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
    public static function rawItems(Request $request): Collection
    {
        return collect($request->session()->get('cart.items', []));
    }

    public static function count(Request $request): int
    {
        return self::rawItems($request)
            ->sum(fn (array $item) => max(0, (int) ($item['quantity'] ?? 0)));
    }

    public static function items(Request $request): Collection
    {
        $cart = self::rawItems($request);

        $productIds = $cart->pluck('product_id')->filter()->all();
        $variantIds = $cart->pluck('variant_id')->filter()->all();
        $fontIds = $cart->pluck('font_id')
            ->merge($cart->pluck('font_selection')->flatten())
            ->filter()
            ->unique()
            ->all();
        $mockupIds = $cart->pluck('mockup_id')->filter()->all();

        $products = Product::with([
            'category',
            'images',
            'personalizationTemplate',
            'personalizationMockups',
            'bundleItems.childProduct.images',
            'bundleItems.childProduct.personalizationTemplate',
            'bundleItems.childProduct.personalizationMockups',
        ])->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
        $fonts = PersonalizationFont::whereIn('id', $fontIds)->get()->keyBy('id');
        $mockups = PersonalizationMockup::whereIn('id', $mockupIds)->get()->keyBy('id');

        return $cart->map(function (array $item) use ($products, $variants, $fonts, $mockups) {
            $product = $products->get($item['product_id']);
            $variant = $item['variant_id'] ? $variants->get($item['variant_id']) : null;
            $font = $item['font_id'] ? $fonts->get($item['font_id']) : null;
            $mockup = $item['mockup_id'] ? $mockups->get($item['mockup_id']) : null;
            $fontSelectionFonts = collect($item['font_selection'] ?? [])
                ->mapWithKeys(fn ($fontId, $fieldKey) => [$fieldKey => $fonts->get($fontId)])
                ->filter();
            $bundleSummary = $product?->type?->value === 'bundle'
                ? ComboPricing::summary($product, $item['bundle_selections'] ?? [])
                : null;
            $unitPrice = $bundleSummary
                ? $bundleSummary['final_total']
                : (float) ($variant?->price ?: $product?->price ?: 0);
            $quantity = (int) $item['quantity'];

            return [
                ...$item,
                'product' => $product,
                'variant' => $variant,
                'font' => $font,
                'font_selection_fonts' => $fontSelectionFonts,
                'mockup' => $mockup,
                'bundle_summary' => $bundleSummary,
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

    public static function sanitizeItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);

                if (! $productId) {
                    return null;
                }

                $variantId = filter_var($item['variant_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $fontId = filter_var($item['font_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $mockupId = filter_var($item['mockup_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $quantity = min(20, max(1, (int) ($item['quantity'] ?? 1)));
                $personalization = collect($item['personalization'] ?? [])
                    ->filter(fn ($value, $key) => is_string($key) && (is_string($value) || is_numeric($value)))
                    ->map(fn ($value) => str((string) $value)->limit(500, '')->toString())
                    ->all();
                $fontSelection = collect($item['font_selection'] ?? [])
                    ->filter(fn ($value, $key) => is_string($key) && filter_var($value, FILTER_VALIDATE_INT))
                    ->map(fn ($value) => (int) $value)
                    ->all();
                $bundleSelections = collect($item['bundle_selections'] ?? [])
                    ->filter(fn ($value) => filter_var($value, FILTER_VALIDATE_INT))
                    ->map(fn ($value) => (int) $value)
                    ->all();
                $key = is_string($item['key'] ?? null) && filled($item['key'])
                    ? str($item['key'])->limit(160, '')->toString()
                    : implode(':', [
                        $productId,
                        $variantId ?: 'base',
                        md5((string) ($item['custom_text'] ?? '')),
                        md5(json_encode($personalization)),
                        $fontId ?: 'no-font',
                        md5(json_encode($fontSelection)),
                        $mockupId ?: 'no-mockup',
                        md5(json_encode($bundleSelections)),
                    ]);

                return [
                    'key' => $key,
                    'product_id' => (int) $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'custom_text' => filled($item['custom_text'] ?? null) ? str((string) $item['custom_text'])->limit(500, '')->toString() : null,
                    'font_id' => $fontId,
                    'font_selection' => $fontSelection,
                    'mockup_id' => $mockupId,
                    'mockup_title' => filled($item['mockup_title'] ?? null) ? str((string) $item['mockup_title'])->limit(160, '')->toString() : null,
                    'proof_note' => filled($item['proof_note'] ?? null) ? str((string) $item['proof_note'])->limit(1000, '')->toString() : null,
                    'personalization' => $personalization,
                    'bundle_selections' => $bundleSelections,
                ];
            })
            ->filter()
            ->take(50)
            ->values()
            ->all();
    }
}
