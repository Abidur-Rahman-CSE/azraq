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

        $regularTotal = $items->sum('line_total');
        $standaloneTotal = $items->sum('standalone_line_total');
        $eligibleSubtotal = $items
            ->filter(fn (array $item) => $item['discount_eligible'] && ! $item['excluded_upgrade'])
            ->sum('standalone_line_total');
        $excludedSubtotal = $items
            ->reject(fn (array $item) => $item['discount_eligible'] && ! $item['excluded_upgrade'])
            ->sum('standalone_line_total');
        $discount = self::discountAmount($bundle, $eligibleSubtotal);
        $finalTotal = self::roundAmount(max(0, $eligibleSubtotal - $discount + $excludedSubtotal), $bundle->combo_rounding_rule ?: 'none');

        if (! filled($bundle->combo_discount_value) && (float) $bundle->price > 0 && $standaloneTotal > (float) $bundle->price) {
            $finalTotal = (float) $bundle->price;
            $discount = max(0, $standaloneTotal - $finalTotal);
        }

        $items = self::applyItemDiscounts(
            $items,
            $bundle->combo_discount_type ?: 'percent',
            self::discountValue($bundle, $standaloneTotal),
            $discount,
            $eligibleSubtotal,
        );

        return [
            'items' => $items->values(),
            'regular_total' => round($regularTotal, 2),
            'standalone_total' => round($standaloneTotal, 2),
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount_type' => $bundle->combo_discount_type ?: 'percent',
            'discount_value' => self::discountValue($bundle, $standaloneTotal),
            'extra_savings_percent' => ($bundle->combo_discount_type ?: 'percent') === 'percent' ? self::discountValue($bundle, $standaloneTotal) : 0,
            'discount_amount' => round($discount, 2),
            'individual_savings_amount' => round(max(0, $regularTotal - $standaloneTotal), 2),
            'bundle_savings_amount' => round(max(0, $standaloneTotal - $finalTotal), 2),
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
        $quantity = max(1, (int) $item->quantity);
        $pricing = self::itemPricing($item, $variant, $quantity);

        return [
            'id' => $item->id,
            'child_product_id' => $product->id,
            'name' => $item->display_label ?: $product->name,
            'product_name' => $product->name,
            'url' => route('products.show', $product),
            'image' => $product->storefront_preview_image_url,
            'quantity' => $quantity,
            'unit_price' => $pricing['standalone_unit_price'],
            'compare_unit_price' => $pricing['compare_unit_price'],
            'standalone_unit_price' => $pricing['standalone_unit_price'],
            'line_total' => $pricing['compare_line_total'],
            'compare_line_total' => $pricing['compare_line_total'],
            'standalone_line_total' => $pricing['standalone_line_total'],
            'default_variant_id' => $variant?->id,
            'default_variant_name' => $variant?->name ?: 'Base',
            'selected_options' => self::variantOptionMap($variant),
            'variant_change_allowed' => (bool) $item->variant_change_allowed || blank($item->default_variant_id),
            'discount_eligible' => (bool) $item->discount_eligible,
            'excluded_upgrade' => (bool) $item->excluded_upgrade,
            'show_on_hero' => (bool) $item->show_on_hero,
            'show_in_details' => (bool) $item->show_in_details,
            'variant_groups' => self::variantGroups($availableVariants),
            'variants' => $availableVariants->map(function (ProductVariant $variant) use ($item, $quantity): array {
                $pricing = self::itemPricing($item, $variant, $quantity);

                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => $pricing['standalone_unit_price'],
                    'compare_price' => $pricing['compare_unit_price'],
                    'line_total' => $pricing['compare_line_total'],
                    'compare_line_total' => $pricing['compare_line_total'],
                    'standalone_line_total' => $pricing['standalone_line_total'],
                    'eligible' => (bool) $item->discount_eligible && ! $item->excluded_upgrade,
                    'option_values' => $variant->option_values ?? [],
                    'option_values_map' => self::variantOptionMap($variant),
                ];
            })->values(),
        ];
    }

    protected static function applyItemDiscounts(Collection $items, string $discountType, float $discountValue, float $discountAmount, float $eligibleSubtotal): Collection
    {
        return $items->map(function (array $item) use ($discountType, $discountValue, $discountAmount, $eligibleSubtotal): array {
            $isEligible = $item['discount_eligible'] && ! $item['excluded_upgrade'];
            $itemDiscount = 0.0;

            $standaloneLineTotal = (float) ($item['standalone_line_total'] ?? $item['line_total']);

            if ($isEligible && $standaloneLineTotal > 0) {
                $itemDiscount = $discountType === 'percent'
                    ? $standaloneLineTotal * ($discountValue / 100)
                    : ($eligibleSubtotal > 0 ? $discountAmount * ($standaloneLineTotal / $eligibleSubtotal) : 0);
            }

            $discountedLineTotal = max(0, $standaloneLineTotal - $itemDiscount);
            $quantity = max(1, (int) $item['quantity']);

            return [
                ...$item,
                'item_discount_amount' => round($itemDiscount, 2),
                'discounted_line_total' => round($discountedLineTotal, 2),
                'discounted_unit_price' => round($discountedLineTotal / $quantity, 2),
                'total_item_savings' => round(max(0, (float) $item['line_total'] - $discountedLineTotal), 2),
            ];
        });
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

    protected static function itemPricing(BundleItem $item, ?ProductVariant $variant, int $quantity): array
    {
        $standaloneUnitPrice = match ($item->price_mode ?: 'add_child_price') {
            'custom_combo_price' => (float) ($item->custom_price ?: 0),
            'upgrade_price_only' => max(0, self::standaloneUnitPrice($item->childProduct, $variant) - self::standaloneUnitPrice($item->childProduct)),
            'included_in_combo_price' => 0.0,
            default => self::standaloneUnitPrice($item->childProduct, $variant),
        };

        $compareUnitPrice = match ($item->price_mode ?: 'add_child_price') {
            'custom_combo_price' => max($standaloneUnitPrice, self::compareUnitPrice($item->childProduct, $variant)),
            'upgrade_price_only' => max(0, self::compareUnitPrice($item->childProduct, $variant) - self::compareUnitPrice($item->childProduct)),
            'included_in_combo_price' => 0.0,
            default => max($standaloneUnitPrice, self::compareUnitPrice($item->childProduct, $variant)),
        };

        return [
            'standalone_unit_price' => $standaloneUnitPrice,
            'compare_unit_price' => $compareUnitPrice,
            'standalone_line_total' => $standaloneUnitPrice * $quantity,
            'compare_line_total' => $compareUnitPrice * $quantity,
        ];
    }

    protected static function standaloneUnitPrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant && (float) $variant->price > 0) {
            return (float) $variant->price;
        }

        return (float) $product->price;
    }

    protected static function compareUnitPrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant && (float) $variant->compare_at_price > 0) {
            return (float) $variant->compare_at_price;
        }

        if ($variant && (float) $variant->price > 0) {
            return (float) $variant->price;
        }

        if ((float) $product->compare_at_price > 0) {
            return (float) $product->compare_at_price;
        }

        return (float) $product->price;
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
