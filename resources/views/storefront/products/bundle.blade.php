@php
    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $bundleItems = $product->bundleItems->filter(fn ($item) => $item->childProduct);
    $comboGallery = $product->images
        ->map(fn ($image) => [
            'url' => $image->image_url,
            'alt' => $image->alt_text ?: $image->label ?: $product->name,
            'label' => $image->label ?: $product->name,
        ])
        ->values();
    $bundlePricing = $bundlePricing ?? \App\Support\ComboPricing::summary($product);
    $bundleReferencePrice = (float) $bundlePricing['regular_total'];
    $bundleSavings = (float) $bundlePricing['savings_amount'];
    $showBundleSavingsBadge = (bool) ($product->show_combo_savings_badge ?? true);
    $bundleRelatedCategories = $product->relatedCategories->isNotEmpty()
        ? $product->relatedCategories->values()
        : collect([$product->category])->filter()->values();
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $product->description)"
    :social-image="$primaryImage?->image_url"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: $product->description),
            'image' => $product->images->pluck('image_url')->filter()->values()->all(),
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'availability' => 'https://schema.org/InStock',
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <div class="space-y-10" x-data="{
        combo: @js($bundlePricing),
        activeImage: @js($comboGallery->first()['url'] ?? $primaryImage?->image_url),
        activeAlt: @js($comboGallery->first()['alt'] ?? $product->name),
        activeIndex: 0,
        formatMoney(value) {
            return `BDT ${Number(value || 0).toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
        },
        recalcCombo() {
            const items = this.combo.items || [];
            const subtotal = items.reduce((sum, item) => sum + (Number(item.line_total) || 0), 0);
            const standaloneSubtotal = items.reduce((sum, item) => sum + (Number(item.standalone_line_total ?? item.line_total) || 0), 0);
            const eligibleSubtotal = items
                .filter((item) => item.discount_eligible && !item.excluded_upgrade)
                .reduce((sum, item) => sum + (Number(item.standalone_line_total ?? item.line_total) || 0), 0);
            const rate = Number(this.combo.discount_value || 0);
            const discount = this.combo.discount_type === 'fixed' ? Math.min(eligibleSubtotal, rate) : eligibleSubtotal * (rate / 100);
            items.forEach((item) => {
                const lineTotal = Number(item.standalone_line_total ?? item.line_total ?? 0);
                const quantity = Math.max(1, Number(item.quantity || 1));
                const eligible = item.discount_eligible && !item.excluded_upgrade;
                const itemDiscount = eligible
                    ? (this.combo.discount_type === 'percent'
                        ? lineTotal * (rate / 100)
                        : (eligibleSubtotal > 0 ? discount * (lineTotal / eligibleSubtotal) : 0))
                    : 0;
                item.item_discount_amount = itemDiscount;
                item.discounted_line_total = Math.max(0, lineTotal - itemDiscount);
                item.discounted_unit_price = item.discounted_line_total / quantity;
            });
            this.combo.regular_total = subtotal;
            this.combo.standalone_total = standaloneSubtotal;
            this.combo.discount_amount = discount;
            this.combo.final_total = Math.max(0, standaloneSubtotal - discount);
            this.combo.savings_amount = Math.max(0, subtotal - this.combo.final_total);
            this.combo.individual_savings_amount = Math.max(0, subtotal - standaloneSubtotal);
            this.combo.bundle_savings_amount = Math.max(0, standaloneSubtotal - this.combo.final_total);
            this.combo.savings_percent = subtotal > 0 ? Math.round((this.combo.savings_amount / subtotal) * 100) : 0;
        },
        selectVariant(itemIndex, variant) {
            const item = this.combo.items[itemIndex];
            item.default_variant_id = variant.id;
            item.default_variant_name = variant.name;
            item.selected_options = variant.option_values_map || {};
            item.unit_price = Number(variant.price || 0);
            item.compare_unit_price = Number(variant.compare_price ?? variant.price ?? 0);
            item.standalone_unit_price = item.unit_price;
            item.line_total = Number(variant.line_total ?? variant.compare_line_total ?? 0);
            item.compare_line_total = Number(variant.compare_line_total ?? variant.line_total ?? 0);
            item.standalone_line_total = Number(variant.standalone_line_total ?? (item.unit_price * Number(item.quantity || 1)));
            this.recalcCombo();
        },
        selectComboOption(itemIndex, groupKey, value) {
            const item = this.combo.items[itemIndex];
            item.selected_options = { ...(item.selected_options || {}), [groupKey]: value };
            const variant = (item.variants || []).find((candidate) => {
                const optionMap = candidate.option_values_map || {};
                return Object.entries(item.selected_options || {}).every(([key, selectedValue]) => optionMap[key] === selectedValue);
            });
            if (variant) {
                this.selectVariant(itemIndex, variant);
            }
        },
        selectedOptionLabel(itemIndex, groupKey) {
            return this.combo.items?.[itemIndex]?.selected_options?.[groupKey] || 'Choose';
        },
        isOptionSelected(itemIndex, groupKey, value) {
            return this.combo.items?.[itemIndex]?.selected_options?.[groupKey] === value;
        },
        swatchColor(value) {
            const normalized = `${value}`.toLowerCase();
            const colors = { black: '#111111', white: '#f8f4ec', brown: '#7a4f35', gold: '#c6a15b', natural: '#d7b98e', ruby: '#8b2635', red: '#8b2635' };
            return colors[normalized] || value;
        },
    }">
        <section class="grid gap-8 lg:grid-cols-[minmax(0,55fr)_minmax(0,45fr)]">
            <div class="lg:self-stretch">
                <div class="space-y-4 lg:sticky lg:top-[88px]">
                    <div class="surface-product overflow-hidden p-4 sm:p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Combo preview</p>
                                <p class="mt-1 text-sm font-medium text-[var(--text-main)]">{{ $bundleItems->sum('quantity') }} curated pieces</p>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($showBundleSavingsBadge && $bundleSavings > 0)
                                    <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-3 py-1 text-[11px] font-medium text-[var(--accent-primary)]">Save <span x-text="combo.savings_percent">{{ $bundlePricing['savings_percent'] }}</span>%</span>
                                @endif
                                @if ($comboGallery->count() > 1)
                                    <span class="rounded-full bg-white/90 px-3 py-1 text-[11px] text-[var(--text-muted)]"><span x-text="activeIndex + 1">1</span>/{{ $comboGallery->count() }}</span>
                                @endif
                            </div>
                        </div>

                        <x-storefront.product-breadcrumbs :product="$product" />

                        <div class="mt-5 overflow-hidden rounded-xl border border-[var(--border-soft)] bg-[var(--bg-section-soft)]">
                            <div class="aspect-[4/5] w-full max-h-[58vh] lg:max-h-[500px]">
                                @if ($primaryImage)
                                    <img :src="activeImage || @js($primaryImage->image_url)" :alt="activeAlt || @js($product->name)" src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->label ?: $product->name }}" class="block h-full w-full object-cover" fetchpriority="high" decoding="async">
                                @else
                                    <div class="flex h-full min-h-[360px] items-center justify-center text-sm text-[var(--text-muted)]">Combo presentation preview</div>
                                @endif
                            </div>
                        </div>

                        @if ($comboGallery->count() > 1)
                            <div class="mt-4 flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                @foreach ($comboGallery as $index => $image)
                                    <button type="button" class="w-[72px] shrink-0" @click="activeImage = @js($image['url']); activeAlt = @js($image['alt']); activeIndex = {{ $index }}" aria-label="Show {{ $image['label'] }}">
                                        <span class="block overflow-hidden rounded-lg border-2 bg-[var(--bg-section-soft)] p-1 transition" :class="activeIndex === {{ $index }} ? 'border-[var(--accent-primary)]' : 'border-transparent'">
                                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-16 w-16 rounded-md object-cover">
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($bundleItems->filter(fn ($item) => $item->show_on_hero)->take(4) as $bundleItem)
                                @php
                                    $child = $bundleItem->childProduct;
                                    $itemImage = $child->storefront_preview_image_url ?: ($child->images->firstWhere('is_primary', true)?->image_url ?: $child->images->first()?->image_url);
                                @endphp
                                <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 p-4">
                                    <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Included item</p>
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="h-16 w-16 overflow-hidden rounded-lg bg-[var(--bg-section-soft)]">
                                            @if ($itemImage)
                                                <img src="{{ $itemImage }}" alt="{{ $child->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-[var(--text-main)]">{{ $child->name }}</p>
                                            <p class="text-xs text-[var(--text-muted)]">Qty {{ $bundleItem->quantity }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <section class="space-y-4 text-[var(--text-main)]">
                <div class="surface-card-featured p-5 sm:p-6">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-[var(--pill-bg)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Curated combo</span>
                        @if ($product->category)
                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-secondary)]">{{ $product->category->name }}</span>
                        @endif
                        @if ($showBundleSavingsBadge && $bundleSavings > 0)
                            <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Save <span x-text="formatMoney(combo.savings_amount)">BDT {{ number_format($bundleSavings, 0) }}</span></span>
                        @endif
                    </div>

                    <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm leading-relaxed text-[var(--text-muted)]">{{ $product->excerpt ?: $product->description }}</p>

                    <div class="mt-4 rounded-xl bg-white/80 p-4">
                        <div class="space-y-2 text-sm text-[var(--text-muted)]">
                            <div class="flex justify-between gap-4">
                                <span>Regular MRP total</span>
                                <span x-text="formatMoney(combo.regular_total)">BDT {{ number_format($bundlePricing['regular_total'], 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span>After product discounts</span>
                                <span x-text="formatMoney(combo.standalone_total)">BDT {{ number_format($bundlePricing['standalone_total'], 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span>Extra bundle saving</span>
                                <span><span x-text="`${Number(combo.extra_savings_percent || combo.discount_value || 0).toLocaleString()}%`">{{ $bundlePricing['extra_savings_percent'] ?: $bundlePricing['discount_value'] }}%</span></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span>Total savings</span>
                                <span x-text="formatMoney(combo.savings_amount)">BDT {{ number_format($bundlePricing['savings_amount'], 0) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-4 border-t border-[var(--border-soft)] pt-4">
                            <span class="text-sm font-medium text-[var(--text-main)]">Final combo price</span>
                            <span class="text-2xl font-semibold text-[var(--accent-primary)]" x-text="formatMoney(combo.final_total)">BDT {{ number_format($bundlePricing['final_total'], 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-5">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Includes</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">{{ $bundleItems->sum('quantity') }} pieces</p>
                        </div>
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Occasion</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">Nikkah set</p>
                        </div>
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Format</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">One cart add</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 text-sm leading-7 text-[var(--text-muted)]">
                        <template x-for="item in combo.items" :key="item.id">
                            <p><span class="font-semibold text-[var(--text-main)]" x-text="item.product_name"></span>: <span x-text="item.default_variant_name"></span> x <span x-text="item.quantity"></span></p>
                        </template>
                    </div>
                </div>

                <form id="order-form" method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainOrderForm">
                    @csrf
                    <template x-for="item in combo.items" :key="`selection-${item.id}`">
                        <input type="hidden" :name="`bundle_selections[${item.id}]`" :value="item.default_variant_id || ''">
                    </template>

                    <div class="surface-card p-5">
                        <h2 class="text-base font-semibold text-[var(--text-main)]">Quantity</h2>
                        <div class="mt-4">
                            <x-storefront.quantity-selector />
                        </div>
                    </div>

                    <div class="surface-card-featured p-5">
                        <button type="submit" class="button-primary w-full !rounded-[var(--radius-xl)] !py-4 !text-base">Add full combo</button>
                        <button type="submit" name="buy_now" value="1" class="button-ghost mt-2 w-full !rounded-[var(--radius-xl)] !py-3.5 !text-sm !text-[var(--accent-primary)]">Buy it now</button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('wishlist.store', $product) }}">
                        @csrf
                        <button type="submit" class="button-ghost">Save combo</button>
                    </form>
                    <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="button-ghost">Explore more combos</a>
                </div>
            </section>
        </section>

        <section class="space-y-6">
            <div class="surface-card p-8">
                <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,40fr)_minmax(0,60fr)]">
                    <div>
                        <p class="mb-3 text-xs uppercase tracking-[0.3em] text-[var(--accent-primary)]">Combo story</p>
                        <h2 class="font-serif text-2xl font-semibold leading-snug text-[var(--text-main)]">A complete ceremonial set without assembling every piece separately</h2>
                    </div>
                    <div class="space-y-4 text-sm leading-7 text-[var(--text-muted)]">
                        <p>{{ $product->description }}</p>
                        <p>This package is curated to feel ceremonial and giftable, not just discounted. Each included item is selected to create a more complete Nikkah table or presentation set.</p>
                    </div>
                </div>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Everything in this combo</h2>
                <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">This combo includes selected default variants. You may upgrade eligible variants before checkout. Existing product discounts stay visible, then the extra bundle saving is applied to eligible selected items.</p>
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @foreach ($bundleItems as $bundleItem)
                        @php
                            $child = $bundleItem->childProduct;
                            $itemImage = $child->storefront_preview_image_url ?: ($child->images->firstWhere('is_primary', true)?->image_url ?: $child->images->first()?->image_url);
                            $pricingIndex = collect($bundlePricing['items'])->search(fn ($item) => (int) $item['id'] === (int) $bundleItem->id);
                            $pricingItem = $pricingIndex !== false ? $bundlePricing['items'][$pricingIndex] : null;
                            $pricingIndex = $pricingIndex === false ? 0 : $pricingIndex;
                            $fallbackLineTotal = (float) ($pricingItem['line_total'] ?? ($child->compare_at_price ?: $child->price));
                            $fallbackStandaloneLineTotal = (float) ($pricingItem['standalone_line_total'] ?? $child->price);
                            $fallbackDiscountedLineTotal = (float) ($pricingItem['discounted_line_total'] ?? $fallbackStandaloneLineTotal);
                        @endphp
                        <article class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                            <div class="flex flex-col gap-5 sm:flex-row">
                                <div class="h-28 w-full overflow-hidden rounded-xl bg-[var(--bg-section-soft)] sm:w-28">
                                    @if ($itemImage)
                                        <img src="{{ $itemImage }}" alt="{{ $child->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-[var(--text-main)]">{{ $child->name }}</h3>
                                        <span class="rounded-full bg-[var(--pill-bg)] px-2 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Qty {{ $bundleItem->quantity }}</span>
                                        @if ($child->type)
                                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2 py-0.5 text-[10px] font-medium text-[var(--accent-secondary)]">{{ $child->type->label() }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs text-[var(--text-muted)]">Included/default variant: <span class="font-semibold text-[var(--text-main)]" x-text="combo.items[{{ $pricingIndex }}]?.default_variant_name || @js($pricingItem['default_variant_name'] ?? 'Base')">{{ $pricingItem['default_variant_name'] ?? 'Base' }}</span></p>
                                    <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">{{ $child->excerpt ?: $child->description }}</p>
                                    @if ($pricingItem && ($pricingItem['variant_change_allowed'] ?? false) && collect($pricingItem['variants'] ?? [])->isNotEmpty())
                                        <div class="mt-4 space-y-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--text-muted)]">Customize variant</p>

                                            @if (collect($pricingItem['variant_groups'] ?? [])->isNotEmpty())
                                                <template x-for="group in combo.items[{{ $pricingIndex }}]?.variant_groups || []" :key="`${combo.items[{{ $pricingIndex }}].id}-${group.key}`">
                                                    <div class="space-y-2">
                                                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                                            <span class="font-serif text-[1.02rem] font-semibold uppercase tracking-[0.035em] text-[var(--accent-secondary)]" x-text="`${group.name}:`"></span>
                                                            <span class="font-serif text-[1.02rem] font-semibold tracking-[0.015em] text-[var(--accent-secondary)]" x-text="selectedOptionLabel({{ $pricingIndex }}, group.key)"></span>
                                                        </div>

                                                        <div class="flex flex-wrap gap-2.5">
                                                            <template x-for="value in group.values" :key="`${group.key}-${value.value}`">
                                                                <button
                                                                    type="button"
                                                                    class="flex items-center gap-1.5 rounded-full border px-5 py-2 text-[0.92rem] font-medium leading-none transition-all duration-200 ease-out"
                                                                    @click="selectComboOption({{ $pricingIndex }}, group.key, value.value)"
                                                                    :class="isOptionSelected({{ $pricingIndex }}, group.key, value.value)
                                                                        ? 'border-[var(--accent-secondary)] bg-[var(--accent-secondary)] text-white shadow-[0_10px_24px_rgba(0,48,73,0.12)]'
                                                                        : 'border-[rgba(0,48,73,0.14)] bg-transparent text-[var(--accent-secondary)] hover:border-[var(--accent-secondary)]'"
                                                                    :title="value.label"
                                                                >
                                                                    <span
                                                                        class="h-2 w-2 rounded-full border border-black/10"
                                                                        :style="`background:${swatchColor(value.swatch || value.value)}`"
                                                                    ></span>
                                                                    <span x-text="value.label"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            @else
                                                <div class="flex flex-wrap gap-2.5">
                                                    <template x-for="variant in combo.items[{{ $pricingIndex }}]?.variants || []" :key="variant.id">
                                                        <button
                                                            type="button"
                                                            class="rounded-full border px-5 py-2 text-[0.92rem] font-medium leading-none transition-all duration-200 ease-out"
                                                            @click="selectVariant({{ $pricingIndex }}, variant)"
                                                            :class="combo.items[{{ $pricingIndex }}]?.default_variant_id === variant.id
                                                                ? 'border-[var(--accent-secondary)] bg-[var(--accent-secondary)] text-white shadow-[0_10px_24px_rgba(0,48,73,0.12)]'
                                                                : 'border-[rgba(0,48,73,0.14)] bg-transparent text-[var(--accent-secondary)] hover:border-[var(--accent-secondary)]'"
                                                            x-text="variant.name"
                                                        ></button>
                                                    </template>
                                                </div>
                                            @endif

                                            <p class="text-xs text-[var(--text-muted)]">Eligible upgrades keep the combo discount. Premium excluded upgrades are shown separately when configured.</p>
                                        </div>
                                    @endif
                                    <div class="mt-4 flex flex-wrap items-center gap-3">
                                        <div class="flex flex-wrap items-baseline gap-2">
                                            <p class="text-sm font-semibold text-[var(--accent-primary)]" x-text="formatMoney(combo.items[{{ $pricingIndex }}]?.discounted_line_total ?? {{ $fallbackDiscountedLineTotal }})">BDT {{ number_format($fallbackDiscountedLineTotal, 0) }}</p>
                                            <p
                                                class="text-xs text-[var(--text-muted)] line-through"
                                                style="{{ $fallbackLineTotal > $fallbackDiscountedLineTotal ? '' : 'display: none;' }}"
                                                x-show="Number(combo.items[{{ $pricingIndex }}]?.line_total ?? 0) > Number(combo.items[{{ $pricingIndex }}]?.discounted_line_total ?? 0)"
                                                x-text="formatMoney(combo.items[{{ $pricingIndex }}]?.line_total ?? {{ $fallbackLineTotal }})"
                                            >BDT {{ number_format($fallbackLineTotal, 0) }}</p>
                                        </div>
                                        <a href="{{ route('products.show', $child) }}" class="text-sm font-semibold text-[var(--accent-primary)] transition hover:underline">View item</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">How combo pricing works</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Regular MRP total</p>
                        <p class="mt-2 text-lg font-semibold text-[var(--text-main)]" x-text="formatMoney(combo.regular_total)">BDT {{ number_format($bundlePricing['regular_total'], 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Product discounts</p>
                        <p class="mt-2 text-lg font-semibold text-[var(--text-main)]" x-text="formatMoney(combo.individual_savings_amount || 0)">BDT {{ number_format($bundlePricing['individual_savings_amount'], 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Extra bundle saving</p>
                        <p class="mt-2 text-lg font-semibold text-[var(--accent-primary)]" x-text="formatMoney(combo.bundle_savings_amount || 0)">BDT {{ number_format($bundlePricing['bundle_savings_amount'], 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                        <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Final price</p>
                        <p class="mt-2 text-lg font-semibold text-[var(--accent-primary)]" x-text="formatMoney(combo.final_total)">BDT {{ number_format($bundlePricing['final_total'], 0) }}</p>
                    </div>
                </div>
                <p class="mt-5 text-sm leading-7 text-[var(--text-muted)]">MRP is used for honest comparison. The final combo price is calculated from the selected items' current selling prices, then the extra bundle saving is applied automatically.</p>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Who this is for</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--text-muted)]">
                        Customers who want a complete ceremonial starter set without assembling each item separately.
                    </div>
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--text-muted)]">
                        Gifting scenarios where value, cohesion, and presentation matter as much as the raw item count.
                    </div>
                </div>
            </div>

            @if (collect($policyRows ?? [])->isNotEmpty())
                <div class="surface-card p-8">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Shipping, care, and policy</h2>
                    <dl class="mt-6 grid gap-4 lg:grid-cols-2">
                        @foreach ($policyRows as $row)
                            <div class="flex items-start justify-between gap-4 border-b border-[var(--border-soft)] py-3">
                                <dt class="text-sm text-[var(--text-muted)]">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-medium text-[var(--text-main)]">{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @if (($faqs ?? collect())->isNotEmpty())
                <div class="surface-card p-8" x-data="{ open: null }">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">FAQ</h2>
                    <div class="mt-4 divide-y divide-[var(--border-soft)]">
                        @foreach ($faqs as $index => $faq)
                            <div>
                                <button type="button" class="flex w-full items-center justify-between gap-4 py-4 text-left text-sm font-semibold text-[var(--text-main)]" @click="open === {{ $index }} ? open = null : open = {{ $index }}">
                                    <span>{{ $faq->question }}</span>
                                    <span class="text-lg text-[var(--accent-primary)]">+</span>
                                </button>
                                <div x-cloak x-show="open === {{ $index }}" x-transition class="pb-4 text-sm leading-7 text-[var(--text-muted)]">
                                    {{ $faq->answer }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($comboGallery->count() > 1)
                <div class="surface-card p-8">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Gallery</h2>
                    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                        @foreach ($comboGallery as $image)
                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="aspect-[4/3] w-full rounded-xl object-cover">
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        @if ($product->relatedProducts->isNotEmpty() || $bundleRelatedCategories->isNotEmpty() || $recentlyViewed->isNotEmpty())
            <section class="space-y-6">
                @if ($product->relatedProducts->isNotEmpty())
                    <div class="surface-card p-8">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Related combos or individual pieces</h2>
                            <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="text-sm text-[var(--accent-primary)] transition hover:underline">Browse combos</a>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                                <x-storefront.listing-card :product="$relatedProduct" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($bundleRelatedCategories->isNotEmpty())
                    <div class="surface-card p-8">
                        <h2 class="mb-6 font-serif text-xl font-semibold text-[var(--text-main)]">Related categories</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($bundleRelatedCategories->take(4) as $category)
                                <a href="{{ route('categories.show', $category) }}" class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 transition hover:border-[var(--accent-primary)]">
                                    <p class="text-sm font-semibold text-[var(--text-main)]">{{ $category->name }}</p>
                                    <p class="mt-2 text-xs leading-6 text-[var(--text-muted)]">{{ $category->storefront_excerpt ?: $category->description }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($recentlyViewed->isNotEmpty())
                    <div class="surface-card p-8">
                        <h2 class="mb-5 text-sm font-medium uppercase tracking-[0.3em] text-[var(--text-muted)]">Last viewed products</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($recentlyViewed as $recentProduct)
                                <x-storefront.listing-card :product="$recentProduct" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-layouts.product-detail>
