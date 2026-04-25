<section class="space-y-4 text-[#3D3730] lg:sticky lg:top-[80px]">
    <div class="rounded-xl border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_20px_rgba(0,0,0,0.05)] sm:p-6">
        <div class="flex flex-wrap gap-2">
            @foreach ($badgeItems as $index => $badge)
                @php
                    $badgeClasses = match ($index) {
                        0 => 'bg-[#F5EDD8] text-[#8B6914]',
                        1 => 'bg-[#F5E6E8] text-[#8B2635]',
                        default => 'bg-[#EEECEA] text-[#5F5C58]',
                    };
                @endphp
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-medium {{ $badgeClasses }}">{{ $badge }}</span>
            @endforeach
        </div>

        <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[#2C2C3E]">{{ $product->name }}</h1>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-2xl font-semibold text-[#8B2635]" x-text="formatMoney(displayPrice)">BDT {{ number_format((float) $product->price, 0) }}</span>
            @if ($product->compare_at_price)
                <span class="text-sm text-[#8C7F74] line-through" x-show="displayComparePrice" x-text="formatMoney(displayComparePrice)">BDT {{ number_format((float) $product->compare_at_price, 0) }}</span>
                <span class="rounded-full bg-[#F5E6E8] px-2 py-0.5 text-xs font-medium text-[#8B2635]" x-show="savePercent > 0" x-text="`SAVE ${savePercent}%`"></span>
            @endif
        </div>

        <p class="mt-2 text-sm leading-relaxed text-[#5F5C58]">{{ $shortDescription }}</p>
    </div>

    <form id="order-form" method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainOrderForm" @submit="submitting = true">
        @csrf

        <div class="rounded-xl border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            @include('products.partials._variant_selectors', [
                'variantGroups' => $variantGroups,
                'simpleVariants' => $simpleVariants,
            ])
        </div>

        <div class="rounded-xl border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            <h2 class="text-base font-semibold text-[#2C2C3E]">Quantity</h2>
            <div class="mt-4 inline-flex items-center overflow-hidden rounded-lg border border-[#E8E3DC]">
                <button type="button" class="px-4 py-2.5 transition duration-200 ease-out hover:bg-[#F5F2EC]" @click="quantity = Math.max(1, quantity - 1)" aria-label="Decrease quantity">−</button>
                <input type="number" min="1" name="quantity" x-model="quantity" class="min-w-[48px] border-0 bg-white px-4 py-2.5 text-center text-sm font-medium text-[#2C2C3E] focus:outline-none focus:ring-0">
                <button type="button" class="px-4 py-2.5 transition duration-200 ease-out hover:bg-[#F5F2EC]" @click="quantity = quantity + 1" aria-label="Increase quantity">+</button>
            </div>
            @error('quantity')
                <p class="mt-2 text-[11px] text-[#8B2635]">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-xl border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_20px_rgba(0,0,0,0.05)]" x-ref="ctaAnchor">
            <button
                type="submit"
                class="relative mt-0 w-full overflow-hidden rounded-xl bg-[#8B2635] py-4 text-base font-medium text-white transition-all duration-200 ease-out hover:bg-[#6D1D29] active:scale-[0.98]"
            >
                <span x-show="!submitting">Add to cart</span>
                <span x-cloak x-show="submitting" class="absolute inset-0 flex items-center justify-center bg-[#8B2635]">
                    <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" class="opacity-90"></path>
                    </svg>
                </span>
            </button>

            <button
                type="submit"
                name="buy_now"
                value="1"
                class="mt-2 w-full rounded-xl border border-[#8B2635] py-3.5 text-sm font-medium text-[#8B2635] transition-all duration-200 ease-out hover:bg-[#F5E6E8]"
            >
                Buy it now
            </button>

            <div class="mt-4 border-t border-[#F0EBE3] pt-4">
                <div class="grid gap-2 text-[11px] text-[#5F5C58] sm:grid-cols-3">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[#C4A882]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.4 11.2 3.2 8l1.1-1.1 2.1 2.1 5-5L12.5 5z"/></svg>
                        <span>Proof before production</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[#C4A882]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 1a3 3 0 0 0-3 3v2H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-1V4a3 3 0 0 0-3-3Zm-1.5 5V4a1.5 1.5 0 0 1 3 0v2h-3Z"/></svg>
                        <span>Secure checkout</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[#C4A882]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 4.5 8 1l6 3.5V12L8 15l-6-3V4.5Zm2 .7V11l4 2.2 4-2.2V5.2L8 3 4 5.2Z"/></svg>
                        <span>Carefully packaged</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
