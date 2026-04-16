@php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $product->description)"
    :social-image="$primaryImage?->image_url"
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
    <div class="space-y-6 lg:sticky lg:top-28 lg:self-start">
        <div class="surface-product overflow-hidden p-6">
            <x-storefront.product-breadcrumbs :product="$product" />

            <div class="mt-6 overflow-hidden rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                @if ($primaryImage)
                    <img src="{{ $primaryImage->image_url }}" alt="{{ $product->name }}" class="h-[420px] w-full object-cover" fetchpriority="high" decoding="async">
                @else
                    <div class="flex h-[420px] items-center justify-center text-[var(--color-text-soft)]">Editorial service visual</div>
                @endif
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Service type</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ str($serviceMeta->service_type)->headline() }}</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Duration</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $serviceMeta->duration_label }}</p>
                </div>
            </div>
        </div>

        <div class="surface-card-soft p-6">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">What this booking includes</h2>
            <div class="mt-5 space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                <p>Coverage area: {{ $serviceMeta->location_scope }}</p>
                <p>{{ $serviceMeta->booking_notes }}</p>
                <p>Once submitted, your request enters the Azraq review queue so date, area, and package fit can be confirmed before final coordination.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="surface-sidebar p-8">
            <div class="flex flex-wrap items-center gap-3">
                <span class="eyebrow">Service / Booking</span>
                @if ($product->category)
                    <x-storefront.trust-badge :label="$product->category->name" />
                @endif
                <x-storefront.trust-badge :label="$serviceMeta->location_scope" />
            </div>

            <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
            <div class="mt-6">
                <x-storefront.price-block :product="$product" />
            </div>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Best for</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Bridal-event planning</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Format</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Request and confirm</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Availability</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Date dependent</p>
                </div>
            </div>

            <div class="mt-6 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm text-[var(--color-secondary-900)]">
                @if ($serviceMeta->requires_advance_payment)
                    Advance payment required after confirmation: BDT {{ number_format((float) $serviceMeta->advance_payment_amount, 0) }}
                @else
                    No advance payment required at the request stage.
                @endif
            </div>

            <form method="POST" action="{{ route('bookings.store', $product) }}" class="mt-8 space-y-5">
                @csrf

                <div class="surface-configurator p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--color-secondary-900)]">Request your date</h2>
                        <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Booking intake</span>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Name</span>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Email</span>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Phone</span>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Preferred date</span>
                            <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Preferred time</span>
                            <input type="text" name="preferred_time" value="{{ old('preferred_time') }}" placeholder="Morning / Afternoon / Evening" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Area / location</span>
                            <input type="text" name="location_area" value="{{ old('location_area') }}" placeholder="{{ $serviceMeta->location_scope }}" class="field-input">
                        </label>
                        <label class="field-shell md:col-span-2">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Package details</span>
                            <input type="text" name="package_details" value="{{ old('package_details') }}" placeholder="Preferred package or scope" class="field-input">
                        </label>
                        <label class="field-shell md:col-span-2">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Notes</span>
                            <textarea name="notes" rows="4" class="field-textarea">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-primary">Submit booking request</button>
                    <a href="{{ route('bookings.index') }}" class="button-ghost">View recent requests</a>
                </div>
            </form>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Booking flow</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">1. Submit your preferred date, time, area, and package notes.</div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">2. Azraq reviews availability, event scope, and any deposit requirements.</div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">3. You receive confirmation and next-step coordination once the request is approved.</div>
            </div>
        </div>
    </div>
</x-layouts.product-detail>
