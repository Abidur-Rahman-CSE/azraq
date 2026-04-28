<?php

namespace App\Support;

use App\Enums\ProductType;
use App\Models\BundleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ComboPricing
{
    public static function summary(Product $bundle, array $selectedVariantIds = []): array
    {
        $bundle->loadMissing([
            'bundleItems.childProduct.images',
            'bundleItems.childProduct.variants',
            'bundleItems.defaultVariant',
        ]);

        $items = $bundle->bundleItems
            ->filter(fn (BundleItem $item) => $item->childProduct)
            ->values()
            ->map(fn (BundleItem $item) => self::itemPayload($item, $selectedVariantIds));

        $eligibleSubtotal = $items
            ->filter(fn (array $item) => $item['discount_eligible'] && ! $item['excluded_upgrade'])
            ->sum('line_total');
        $excludedSubtotal = $items
            ->reject(fn (array $item) => $item['discount_eligible'] && ! $item['excluded_upgrade'])
            ->sum('line_total');
        $regularTotal = $items->sum('line_total');
        $discount = self::discountAmount($bundle, $eligibleSubtotal);
        $finalTotal = self::roundAmount(max(0, $eligibleSubtotal - $discount + $excludedSubtotal), $bundle->combo_rounding_rule ?: 'none');

        if (! filled($bundle->combo_discount_value) && (float) $bundle->price > 0 && $regularTotal > (float) $bundle->price) {
            $finalTotal = (float) $bundle->price;
            $discount = max(0, $regularTotal - $finalTotal);
        }

        return [
            'items' => $items->values(),
            'regular_total' => round($regularTotal, 2),
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount_type' => $bundle->combo_discount_type ?: 'percent',
            'discount_value' => self::discountValue($bundle, $regularTotal),
            'discount_amount' => round($discount, 2),
            'savings_amount' => round(max(0, $regularTotal - $finalTotal), 2),
            'savings_percent' => $regularTotal > 0 ? (int) round(max(0, ($regularTotal - $finalTotal) / $regularTotal) * 100) : 0,
            'final_total' => round($finalTotal, 2),
        ];
    }

    public static function suggestionsForProduct(Product $product, int $limit = 3): Collection
    {
        $directBundleIds = $product->includedInBundles()
            ->pluck('bundle_product_id')
            ->all();

        $query = Product::query()
            ->with(['category', 'images', 'bundleItems.childProduct.images', 'bundleItems.childProduct.variants'])
            ->where('status', 'active')
            ->where('type', ProductType::Bundle->value);

        $direct = (clone $query)
            ->whereIn('id', $directBundleIds ?: [-1])
            ->get();

        $needed = max(0, $limit - $direct->count());
        $fallback = collect();

        if ($needed > 0) {
            $relatedCategoryIds = $product->relatedCategories()->pluck('categories.id')->all();

            $fallback = (clone $query)
                ->whereNotIn('id', $direct->pluck('id')->all())
                ->where(function ($builder) use ($product, $relatedCategoryIds): void {
                    $builder->where('category_id', $product->category_id);

                    if ($relatedCategoryIds) {
                        $builder->orWhereIn('category_id', $relatedCategoryIds);
                    }
                })
                ->orderByDesc('is_featured')
                ->limit($needed)
                ->get();
        }

        $needed = max(0, $limit - $direct->count() - $fallback->count());
        $featured = $needed > 0
            ? (clone $query)
                ->whereNotIn('id', $direct->merge($fallback)->pluck('id')->all())
                ->orderByDesc('is_featured')
                ->limit($needed)
                ->get()
            : collect();

        return $direct->merge($fallback)->merge($featured)->take($limit)->values();
    }

    protected static function itemPayload(BundleItem $item, array $selectedVariantIds): array
    {
        $product = $item->childProduct;
        $allowedVariantIds = collect($item->allowed_variant_ids ?? [])->map(fn ($id) => (int) $id)->filter();
        $availableVariants = $product->variants
            ->when($allowedVariantIds->isNotEmpty(), fn ($variants) => $variants->whereIn('id', $allowedVariantIds->all()))
            ->values();
        $selectedVariantId = (int) ($selectedVariantIds[$item->id] ?? $item->default_variant_id ?? 0);
        $variant = $availableVariants->firstWhere('id', $selectedVariantId)
            ?: $item->defaultVariant
            ?: $product->variants->firstWhere('is_default', true)
            ?: $product->variants->first();
        $unitPrice = self::unitPrice($item, $variant);
        $quantity = max(1, (int) $item->quantity);

        return [
            'id' => $item->id,
            'child_product_id' => $product->id,
            'name' => $item->display_label ?: $product->name,
            'product_name' => $product->name,
            'url' => route('products.show', $product),
            'image' => $product->storefront_preview_image_url,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
            'default_variant_id' => $variant?->id,
            'default_variant_name' => $variant?->name ?: 'Base',
            'selected_options' => self::variantOptionMap($variant),
            'variant_change_allowed' => (bool) $item->variant_change_allowed || blank($item->default_variant_id),
            'discount_eligible' => (bool) $item->discount_eligible,
            'excluded_upgrade' => (bool) $item->excluded_upgrade,
            'show_on_hero' => (bool) $item->show_on_hero,
            'show_in_details' => (bool) $item->show_in_details,
            'variant_groups' => self::variantGroups($availableVariants),
            'variants' => $availableVariants->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => (float) ($variant->price ?: $product->price),
                'eligible' => (bool) $item->discount_eligible && ! $item->excluded_upgrade,
                'option_values' => $variant->option_values ?? [],
                'option_values_map' => self::variantOptionMap($variant),
            ])->values(),
        ];
    }

    protected static function variantGroups(Collection $variants): Collection
    {
        return $variants
            ->reduce(function (Collection $groups, ProductVariant $variant): Collection {
                foreach (self::variantOptionMap($variant) as $key => $value) {
                    if (! $groups->has($key)) {
                        $groups->put($key, [
                            'key' => $key,
                            'name' => str($key)->replace('_', ' ')->headline()->toString(),
                            'values' => collect(),
                        ]);
                    }

                    $group = $groups->get($key);

                    if (! $group['values']->contains(fn (array $groupValue) => $groupValue['value'] === $value)) {
                        $group['values']->push([
                            'label' => $value,
                            'value' => $value,
                            'swatch' => $value,
                        ]);
                    }

                    $groups->put($key, $group);
                }

                return $groups;
            }, collect())
            ->map(fn (array $group) => [
                ...$group,
                'values' => $group['values']->values(),
            ])
            ->values();
    }

    protected static function variantOptionMap(?ProductVariant $variant): array
    {
        if (! $variant) {
            return [];
        }

        return collect($variant->option_values ?? [])
            ->filter(fn ($entry) => is_string($entry) && str_contains($entry, ':'))
            ->mapWithKeys(function (string $entry): array {
                [$key, $value] = array_pad(explode(':', $entry, 2), 2, '');

                return [str($key)->trim()->replace(' ', '_')->lower()->toString() => trim($value)];
            })
            ->filter()
            ->all();
    }

    protected static function unitPrice(BundleItem $item, ?ProductVariant $variant): float
    {
        return match ($item->price_mode ?: 'add_child_price') {
            'custom_combo_price' => (float) ($item->custom_price ?: 0),
            'upgrade_price_only' => max(0, (float) ($variant?->price ?: 0) - (float) $item->childProduct->price),
            'included_in_combo_price' => 0.0,
            default => (float) ($variant?->price ?: $item->childProduct->price),
        };
    }

    protected static function discountAmount(Product $bundle, float $eligibleSubtotal): float
    {
        if (! filled($bundle->combo_discount_value)) {
            return 0.0;
        }

        return ($bundle->combo_discount_type ?: 'percent') === 'fixed'
            ? min($eligibleSubtotal, (float) $bundle->combo_discount_value)
            : $eligibleSubtotal * ((float) $bundle->combo_discount_value / 100);
    }

    protected static function discountValue(Product $bundle, float $regularTotal): float
    {
        if (filled($bundle->combo_discount_value)) {
            return (float) $bundle->combo_discount_value;
        }

        if ((float) $bundle->price > 0 && $regularTotal > (float) $bundle->price) {
            return round((($regularTotal - (float) $bundle->price) / $regularTotal) * 100, 2);
        }

        return 0.0;
    }

    protected static function roundAmount(float $amount, string $rule): float
    {
        $step = match ($rule) {
            'nearest_10' => 10,
            'nearest_50' => 50,
            'nearest_100' => 100,
            default => 0,
        };

        return $step > 0 ? round($amount / $step) * $step : $amount;
    }
}
