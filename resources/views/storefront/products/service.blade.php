@php
    use Illuminate\Support\Str;

    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $serviceImage = $product->storefront_preview_image_url ?: $primaryImage?->image_url;
    $serviceGallery = $product->images
        ->map(fn ($image) => [
            'url' => $image->image_url,
            'alt' => $image->alt_text ?: $image->label ?: $product->name,
            'label' => $image->label ?: $product->name,
        ])
        ->values();
    $shortDescription = $product->excerpt ?: Str::limit(strip_tags($product->description), 150);
    $relatedServiceProducts = ($relatedProducts ?? $product->relatedProducts ?? collect())->values();
    $serviceRelatedCategories = ($relatedCategories ?? $product->relatedCategories ?? collect())->isNotEmpty()
        ? ($relatedCategories ?? $product->relatedCategories)->values()
        : collect([$product->category])->filter()->values();
    $includeItems = collect($serviceMeta->include_items ?: [])
        ->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null))
        ->whenEmpty(fn ($items) => $items->push([
            'title' => $serviceMeta->service_type ?: 'Personal service consultation',
            'description' => $serviceMeta->booking_notes ?: $product->description,
        ]));
    $packages = collect($serviceMeta->packages ?: [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null));
    $beforeAppointment = collect($serviceMeta->before_appointment ?: [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null));
    $pricingNotes = collect($serviceMeta->pricing_notes ?: [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null));
    $policies = collect($policyRows ?? ($serviceMeta->policies ?: []))
        ->map(fn ($item) => [
            'title' => $item['title'] ?? $item['label'] ?? 'Policy',
            'description' => $item['description'] ?? $item['value'] ?? '',
        ])
        ->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null));
    $serviceFaqs = ($product->product_faqs || collect($serviceMeta->faqs ?: [])->isEmpty())
        ? collect($faqs ?? [])->map(fn ($faq) => [
            'title' => data_get($faq, 'question', 'Question'),
            'description' => data_get($faq, 'answer', ''),
        ])
        : collect($serviceMeta->faqs ?: []);
    $serviceFaqs = $serviceFaqs->filter(fn ($item) => filled($item['title'] ?? $item['question'] ?? null) || filled($item['description'] ?? $item['answer'] ?? null));
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $product->description)"
    :social-image="$serviceImage"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: $product->description),
            'provider' => [
                '@type' => 'Organization',
                'name' => config('brand.name'),
            ],
            'areaServed' => $serviceMeta->location_scope,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <div class="space-y-10">
        <section class="grid gap-8 lg:grid-cols-[minmax(0,55fr)_minmax(0,45fr)]">
            <div class="lg:self-stretch">
                <div class="space-y-4 lg:sticky lg:top-[88px]" x-data="{ activeImage: @js($serviceGallery->first()['url'] ?? $serviceImage), activeAlt: @js($serviceGallery->first()['alt'] ?? $product->name), activeIndex: 0 }">
                    <div class="surface-product overflow-hidden p-4 sm:p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Service preview</p>
                                <p class="mt-1 text-sm font-medium text-[var(--text-main)]">{{ $serviceMeta->service_type ?: $product->name }}</p>
                            </div>
                            @if ($serviceGallery->count() > 1)
                                <span class="rounded-full bg-white/90 px-3 py-1 text-[11px] text-[var(--text-muted)]"><span x-text="activeIndex + 1">1</span>/{{ $serviceGallery->count() }}</span>
                            @endif
                        </div>

                        <x-storefront.product-breadcrumbs :product="$product" />

                        <div class="mt-5 overflow-hidden rounded-xl border border-[var(--border-soft)] bg-[var(--bg-section-soft)]">
                            <div class="aspect-[4/5] w-full max-h-[58vh] lg:max-h-[500px]">
                                @if ($serviceImage)
                                    <img :src="activeImage || @js($serviceImage)" :alt="activeAlt || @js($product->name)" src="{{ $serviceImage }}" alt="{{ $product->name }}" class="block h-full w-full object-cover" fetchpriority="high" decoding="async">
                                @else
                                    <div class="flex h-full min-h-[360px] items-center justify-center text-sm text-[var(--text-muted)]">Service visual</div>
                                @endif
                            </div>
                        </div>

                        @if ($serviceGallery->count() > 1)
                            <div class="mt-4 flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                @foreach ($serviceGallery as $index => $image)
                                    <button type="button" class="w-[72px] shrink-0" @click="activeImage = @js($image['url']); activeAlt = @js($image['alt']); activeIndex = {{ $index }}" aria-label="Show {{ $image['label'] }}">
                                        <span class="block overflow-hidden rounded-lg border-2 bg-[var(--bg-section-soft)] p-1 transition" :class="activeIndex === {{ $index }} ? 'border-[var(--accent-primary)]' : 'border-transparent'">
                                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-16 w-16 rounded-md object-cover">
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Duration</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">{{ $serviceMeta->duration_label ?: 'Confirmed after request' }}</p>
                            </div>
                            <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Location</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">{{ $serviceMeta->location_scope ?: 'Flexible by request' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="space-y-4 text-[var(--text-main)]">
                <div class="surface-card-featured p-5 sm:p-6">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-[var(--pill-bg)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Service / Booking</span>
                        @if ($product->category)
                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-secondary)]">{{ $product->category->name }}</span>
                        @endif
                        @if ($serviceMeta->requires_advance_payment)
                            <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Advance required</span>
                        @endif
                    </div>

                    <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h1>
                    <div class="mt-3">
                        <span class="text-2xl font-semibold text-[var(--accent-primary)]">BDT {{ number_format((float) $product->price, 0) }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-[var(--text-muted)]">{{ $shortDescription }}</p>
                </div>

                <div class="surface-card p-5">
                    <h2 class="text-base font-semibold text-[var(--text-main)]">Quick facts</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            ['label' => 'Duration', 'value' => $serviceMeta->duration_label ?: 'Confirmed after request'],
                            ['label' => 'Location', 'value' => $serviceMeta->location_scope ?: 'Flexible by request'],
                            ['label' => 'Advance', 'value' => $serviceMeta->requires_advance_payment ? 'BDT '.number_format((float) $serviceMeta->advance_payment_amount, 0) : 'After confirmation'],
                            ['label' => 'Confirmation', 'value' => $serviceMeta->confirmation_note ?: 'Availability checked before payment'],
                        ] as $fact)
                            <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">{{ $fact['label'] }}</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">{{ $fact['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <form id="order-form" method="POST" action="{{ route('bookings.store', $product) }}" class="space-y-4" x-ref="mainOrderForm">
                    @csrf

                    <div class="surface-card p-5">
                        <h2 class="text-base font-semibold text-[var(--text-main)]">Request your date</h2>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Name</span>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="field-input">
                            </label>
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Email</span>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="field-input">
                            </label>
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Phone</span>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="field-input">
                            </label>
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Preferred date</span>
                                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" class="field-input">
                            </label>
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Preferred time</span>
                                <input type="text" name="preferred_time" value="{{ old('preferred_time') }}" placeholder="Morning / Afternoon / Evening" class="field-input">
                            </label>
                            <label class="field-shell">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Area / location</span>
                                <input type="text" name="location_area" value="{{ old('location_area') }}" placeholder="{{ $serviceMeta->location_scope }}" class="field-input">
                            </label>
                            <label class="field-shell sm:col-span-2">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Package details</span>
                                <input type="text" name="package_details" value="{{ old('package_details') }}" placeholder="Preferred package or scope" class="field-input">
                            </label>
                            <label class="field-shell sm:col-span-2">
                                <span class="text-sm font-semibold text-[var(--text-main)]">Notes</span>
                                <textarea name="notes" rows="4" class="field-textarea">{{ old('notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="surface-card-featured p-5">
                        @if ($serviceMeta->requires_advance_payment)
                            <p class="mb-4 text-sm leading-7 text-[var(--text-muted)]">Advance after confirmation: BDT {{ number_format((float) $serviceMeta->advance_payment_amount, 0) }}</p>
                        @else
                            <p class="mb-4 text-sm leading-7 text-[var(--text-muted)]">No advance payment is required at the request stage.</p>
                        @endif

                        <button type="submit" class="button-primary w-full !rounded-[var(--radius-xl)] !py-4 !text-base">Submit booking request</button>
                        <a href="{{ route('bookings.index') }}" class="button-ghost mt-2 w-full !rounded-[var(--radius-xl)] !py-3.5 !text-sm !text-[var(--accent-primary)]">View recent requests</a>
                    </div>
                </form>
            </section>
        </section>

        <section class="space-y-6">
            <div class="surface-card p-8">
                <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,40fr)_minmax(0,60fr)]">
                    <div>
                        <p class="mb-3 text-xs uppercase tracking-[0.3em] text-[var(--accent-primary)]">Service details</p>
                        <h2 class="font-serif text-2xl font-semibold leading-snug text-[var(--text-main)]">What this booking includes</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($includeItems as $item)
                            <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                                <p class="text-sm font-semibold text-[var(--text-main)]">{{ $item['title'] ?? 'Included' }}</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $item['description'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($packages->isNotEmpty())
                <div class="surface-card p-8">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Available packages / service scopes</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($packages as $package)
                            <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                                <p class="text-sm font-semibold text-[var(--text-main)]">{{ $package['title'] ?? 'Package' }}</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $package['description'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Booking flow</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach (collect($serviceMeta->booking_flow ?: [])->isNotEmpty() ? collect($serviceMeta->booking_flow) : collect([
                        ['title' => 'Submit your request', 'copy' => 'Share your preferred date, time, area, and service notes.'],
                        ['title' => 'Confirm availability', 'copy' => 'Azraq reviews availability and confirms the right package scope.'],
                        ['title' => 'Advance payment', 'copy' => 'Advance payment is requested after confirmation when required.'],
                        ['title' => 'Coordinate next steps', 'copy' => 'You receive final coordination before the appointment.'],
                    ]) as $index => $step)
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[rgba(120,0,0,0.08)] text-sm font-semibold text-[var(--accent-primary)]">{{ $index + 1 }}</span>
                            <p class="mt-4 text-sm font-semibold text-[var(--text-main)]">{{ $step['title'] ?? 'Step' }}</p>
                            <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $step['description'] ?? $step['copy'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @foreach ([
                ['title' => 'Before your appointment', 'items' => $beforeAppointment],
                ['title' => 'Pricing and extra charges', 'items' => $pricingNotes],
                ['title' => 'Policy', 'items' => $policies],
            ] as $section)
                @if ($section['items']->isNotEmpty())
                    <div class="surface-card p-8">
                        <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">{{ $section['title'] }}</h2>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($section['items'] as $item)
                                <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                                    <p class="text-sm font-semibold text-[var(--text-main)]">{{ $item['title'] ?? $section['title'] }}</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $item['description'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($serviceFaqs->isNotEmpty())
                <div class="surface-card p-8" x-data="{ open: null }">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">FAQ</h2>
                    <div class="mt-4 divide-y divide-[var(--border-soft)]">
                        @foreach ($serviceFaqs as $index => $faq)
                            <div>
                                <button type="button" class="flex w-full items-center justify-between gap-4 py-4 text-left text-sm font-semibold text-[var(--text-main)]" @click="open === {{ $index }} ? open = null : open = {{ $index }}">
                                    <span>{{ $faq['title'] ?? $faq['question'] ?? 'Question' }}</span>
                                    <span class="text-lg text-[var(--accent-primary)]">+</span>
                                </button>
                                <div x-cloak x-show="open === {{ $index }}" x-transition class="pb-4 text-sm leading-7 text-[var(--text-muted)]">
                                    {{ $faq['description'] ?? $faq['answer'] ?? '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($serviceGallery->count() > 1)
                <div class="surface-card p-8">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Gallery / service moments</h2>
                    @if ($serviceMeta->gallery_intro_text)
                        <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $serviceMeta->gallery_intro_text }}</p>
                    @endif
                    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                        @foreach ($serviceGallery as $image)
                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="aspect-[4/3] w-full rounded-xl object-cover">
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        @if ($relatedServiceProducts->isNotEmpty() || $serviceRelatedCategories->isNotEmpty() || ($recentlyViewed ?? collect())->isNotEmpty())
            <section class="space-y-6">
            @if ($relatedServiceProducts->isNotEmpty())
            <div class="surface-card p-8">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Related services and products</h2>
                    @if ($product->category)
                        <a href="{{ route('categories.show', $product->category) }}" class="text-sm text-[var(--accent-primary)] transition hover:underline">Browse {{ $product->category->name }}</a>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedServiceProducts->take(4) as $relatedProduct)
                        <x-storefront.listing-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
            @endif

            @if ($serviceRelatedCategories->isNotEmpty())
                <div class="surface-card p-8">
                    <h2 class="mb-6 font-serif text-xl font-semibold text-[var(--text-main)]">Related categories</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($serviceRelatedCategories as $category)
                            <a href="{{ route('categories.show', $category) }}" class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 transition hover:border-[var(--accent-primary)]">
                                <p class="text-sm font-semibold text-[var(--text-main)]">{{ $category->name }}</p>
                                <p class="mt-2 text-xs leading-6 text-[var(--text-muted)]">{{ $category->storefront_excerpt ?: $category->description }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (($recentlyViewed ?? collect())->isNotEmpty())
                <div class="surface-card p-8">
                    <h2 class="mb-6 text-sm font-medium uppercase tracking-[0.3em] text-[var(--text-muted)]">Last viewed products</h2>
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
