<section class="mt-16 space-y-6 lg:col-span-2">
    <div id="shipping-care-policy" class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
        <div class="grid items-center gap-8 lg:grid-cols-2">
            @if ($storyVisual)
                <div class="relative overflow-hidden rounded-xl aspect-[4/3]">
                    <img src="{{ $storyVisual }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-700 ease-out hover:scale-105">
                </div>
            @endif

            <div>
                <p class="mb-3 text-xs uppercase tracking-[0.3em] text-[#C4A882]">Product story</p>
                <h2 class="mb-4 font-serif text-2xl font-semibold leading-snug text-[#2C2C3E]">
                    {{ $product->is_customizable ? 'A keepsake designed for ceremonial display' : 'A considered detail for bridal gifting and display' }}
                </h2>
                <div class="prose prose-sm max-w-none text-[#5F5C58] prose-headings:text-[#2C2C3E] prose-p:text-[#5F5C58] prose-a:text-[#8B2635]">
                    {!! $product->description !!}
                </div>
            </div>
        </div>
    </div>

    @if ($product->is_customizable)
        <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            <h3 class="mb-6 font-serif text-xl font-semibold text-[#2C2C3E]">What&apos;s included</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Personalised certificate', 'copy' => 'A bespoke nikah nama proof and print'],
                    ['label' => 'Digital proof review', 'copy' => 'A designer checks the proof before production'],
                    ['label' => 'Premium 250gsm print', 'copy' => 'Rich heavyweight stock for ceremonial display'],
                    ['label' => 'Framing upgrade', 'copy' => 'Optional framing finishes for gifting or display'],
                ] as $item)
                    <div class="rounded-xl border border-[#E8E3DC] bg-[#FAFAF8] p-5">
                        <svg class="h-5 w-5 text-[#C4A882]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2 3 5.5v5c0 4.2 2.9 6.8 7 8 4.1-1.2 7-3.8 7-8v-5L10 2Zm0 2.1 5 2.4v4c0 3.1-2 5.1-5 6.1-3-1-5-3-5-6.1v-4l5-2.4Z"/></svg>
                        <p class="mt-3 text-sm font-medium text-[#2C2C3E]">{{ $item['label'] }}</p>
                        <p class="mt-1 text-xs text-[#8C7F74]">{{ $item['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            <h3 class="mb-6 font-serif text-xl font-semibold text-[#2C2C3E]">How it works</h3>
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ([
                    ['title' => 'Enter your details', 'copy' => 'Add names, dates, and any wording preferences for the proof.'],
                    ['title' => 'Review your digital proof', 'copy' => 'See the live preview and confirm the composition before print.'],
                    ['title' => 'Approve and we print', 'copy' => 'Once approved, we finish, package, and dispatch your keepsake.'],
                ] as $index => $step)
                    <div class="relative rounded-xl border border-[#E8E3DC] bg-[#FAFAF8] p-5">
                        <span class="mb-4 flex h-9 w-9 items-center justify-center rounded-full bg-[#F5E6E8] text-sm font-semibold text-[#8B2635]">{{ $index + 1 }}</span>
                        <p class="text-sm font-medium text-[#2C2C3E]">{{ $step['title'] }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-[#5F5C58]">{{ $step['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
        <h3 class="mb-6 font-serif text-xl font-semibold text-[#2C2C3E]">Shipping, care, and policy</h3>
        <dl class="grid gap-4 lg:grid-cols-2">
            @foreach ($deliveryRows as $row)
                <div class="flex items-start justify-between gap-4 border-b border-[#F0EBE3] py-3">
                    <dt class="text-sm text-[#8C7F74]">{{ $row['label'] }}</dt>
                    <dd class="text-right text-sm font-medium text-[#3D3730]">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-4 text-sm text-[#8C7F74]">All items are gift-ready wrapped and carefully posted.</p>
    </div>

    @if ($faqs->isNotEmpty())
        <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]" x-data="{ open: null }">
            <h3 class="mb-2 font-serif text-xl font-semibold text-[#2C2C3E]">FAQ</h3>
            <div>
                @foreach ($faqs as $index => $faq)
                    <div>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 py-4 text-left text-sm font-medium text-[#3D3730] transition duration-200 ease-out hover:text-[#8B2635]"
                            @click="open === {{ $index }} ? open = null : open = {{ $index }}"
                        >
                            <span>{{ $faq->question }}</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="open === {{ $index }} ? 'rotate-45' : ''" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M7 1h2v6h6v2H9v6H7V9H1V7h6V1Z"/></svg>
                        </button>
                        <div x-cloak x-show="open === {{ $index }}" x-transition.duration.200ms class="pb-4 text-sm leading-relaxed text-[#5F5C58]">
                            {{ $faq->answer }}
                        </div>
                        @if (! $loop->last)
                            <div class="h-px bg-[#F0EBE3]"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($relatedCategories->isNotEmpty())
        <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            <h3 class="mb-6 font-serif text-xl font-semibold text-[#2C2C3E]">Browse the collection</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($relatedCategories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="group relative overflow-hidden rounded-xl aspect-[3/2]">
                        <img src="{{ $category->banner_image_url ?: $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/55 to-black/10"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                            <p class="font-serif text-base font-medium">{{ $category->name }}</p>
                            <p class="text-xs text-white/70">{{ $category->products()->count() }} items</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($relatedProducts->isNotEmpty())
        <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]">
            <div class="mb-6 flex items-center justify-between gap-4">
                <h3 class="font-serif text-xl font-semibold text-[#2C2C3E]">You might also like</h3>
                <a href="{{ route('categories.show', $product->category) }}" class="text-sm text-[#8B2635] transition duration-200 ease-out hover:underline">Browse all →</a>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($relatedProducts->take(4) as $relatedProduct)
                    @php
                        $relatedImage = $relatedProduct->featured_image_url ?: optional($relatedProduct->images->first())->image_url;
                    @endphp
                    <a href="{{ route('products.show', $relatedProduct) }}" class="group overflow-hidden rounded-xl border border-[#E8E3DC] bg-white transition-shadow duration-200 ease-out hover:shadow-[0_4px_20px_rgba(0,0,0,0.08)]">
                        <div class="relative overflow-hidden aspect-[3/4]">
                            <img src="{{ $relatedImage }}" alt="{{ $relatedProduct->name }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                            @if (filled($relatedProduct->category?->name))
                                <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[10px] font-medium text-[#8B2635]">{{ $relatedProduct->category->name }}</span>
                            @endif
                            <span class="absolute bottom-2 left-2 right-2 translate-y-3 rounded-lg bg-white/90 py-1.5 text-center text-xs text-[#3D3730] opacity-0 transition-all duration-200 ease-out group-hover:translate-y-0 group-hover:opacity-100">Quick view</span>
                        </div>
                        <div class="p-3">
                            <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-[#8B2635]">{{ $relatedProduct->category?->name ?: 'Collection' }}</p>
                            <p class="mt-0.5 line-clamp-2 text-sm font-medium leading-snug text-[#2C2C3E]">{{ $relatedProduct->name }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-sm font-semibold text-[#8B2635]">BDT {{ number_format((float) $relatedProduct->price, 0) }}</span>
                                @if ($relatedProduct->compare_at_price)
                                    <span class="text-xs text-[#8C7F74] line-through">BDT {{ number_format((float) $relatedProduct->compare_at_price, 0) }}</span>
                                @endif
                            </div>
                            <span class="mt-1 block text-xs text-[#8C7F74] transition duration-200 ease-out group-hover:text-[#8B2635]">View details</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_20px_rgba(0,0,0,0.05)]" x-show="recentlyViewedItems.length" x-cloak>
        <h3 class="mb-4 text-sm font-medium uppercase tracking-[0.3em] text-[#8C7F74]">Recently viewed</h3>
        <div class="flex gap-3 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <template x-for="item in recentlyViewedItems" :key="item.id">
                <a :href="item.url" class="w-[80px] shrink-0 group">
                    <img :src="item.image" :alt="item.name" class="aspect-square w-full rounded-lg border border-[#E8E3DC] object-cover transition duration-200 ease-out group-hover:border-[#C4A882]">
                    <p class="mt-1 truncate text-center text-[10px] text-[#8C7F74]" x-text="item.name"></p>
                </a>
            </template>
        </div>
    </div>
</section>
