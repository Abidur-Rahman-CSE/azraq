@php
    use Illuminate\Support\Str;

    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $serviceImage = $product->storefront_preview_image_url ?: $primaryImage?->image_url;
    $shortDescription = $product->excerpt ?: Str::limit(strip_tags($product->description), 150);
    $relatedServiceProducts = ($relatedProducts ?? $product->relatedProducts ?? collect())->values();
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
                <div class="space-y-4 lg:sticky lg:top-[88px]">
                    <div class="surface-product overflow-hidden p-4 sm:p-5">
                        <div class="mb-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Service preview</p>
                            <p class="mt-1 text-sm font-medium text-[var(--text-main)]">{{ $serviceMeta->service_type ?: $product->name }}</p>
                        </div>

                        <x-storefront.product-breadcrumbs :product="$product" />

                        <div class="mt-5 overflow-hidden rounded-xl border border-[var(--border-soft)] bg-[var(--bg-section-soft)]">
                            <div class="aspect-[4/5] w-full max-h-[58vh] lg:max-h-[500px]">
                                @if ($serviceImage)
                                    <img src="{{ $serviceImage }}" alt="{{ $product->name }}" class="block h-full w-full object-cover" fetchpriority="high" decoding="async">
                                @else
                                    <div class="flex h-full min-h-[360px] items-center justify-center text-sm text-[var(--text-muted)]">Service visual</div>
                                @endif
                            </div>
                        </div>

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
                    <div class="space-y-4 text-sm leading-7 text-[var(--text-muted)]">
                        <p>{{ $serviceMeta->booking_notes ?: $product->description }}</p>
                        <p>After submitting, Azraq reviews availability, area, and package fit before confirming the service.</p>
                    </div>
                </div>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Booking flow</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach ([
                        ['title' => 'Submit your request', 'copy' => 'Share your preferred date, time, area, and service notes.'],
                        ['title' => 'Confirm availability', 'copy' => 'Azraq reviews availability and confirms the right package scope.'],
                        ['title' => 'Coordinate next steps', 'copy' => 'You receive confirmation, payment guidance, and final coordination.'],
                    ] as $index => $step)
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[rgba(120,0,0,0.08)] text-sm font-semibold text-[var(--accent-primary)]">{{ $index + 1 }}</span>
                            <p class="mt-4 text-sm font-semibold text-[var(--text-main)]">{{ $step['title'] }}</p>
                            <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $step['copy'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($relatedServiceProducts->isNotEmpty())
            <section class="surface-card p-8">
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
            </section>
        @endif
    </div>
</x-layouts.product-detail>
