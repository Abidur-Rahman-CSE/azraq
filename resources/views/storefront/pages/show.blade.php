<x-layouts.narrow
    :title="($page->meta_title ?: $page->title).' | '.config('brand.name')"
    :description="$page->meta_description"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->title,
            'url' => route('pages.show', $page),
            'description' => $page->meta_description ?: $page->body,
        ],
    ]"
>
    <div class="space-y-6">
        @if ($pageKind === 'about')
            <section class="surface-card-featured p-8">
                <span class="eyebrow">About</span>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $page->title }}</h1>
                <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">{{ $page->body }}</p>
            </section>

            <section class="grid gap-6 md:grid-cols-2">
                <div class="surface-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Philosophy</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Warm, calm, and ceremonial</h2>
                    <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">Azraq Bridal is designed around keepsakes, gifting, and personalized ceremonial details that should feel premium without becoming overwhelming.</p>
                </div>
                <div class="surface-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Personalization value</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Thoughtful details matter</h2>
                    <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">From structured Nikah products to compact custom gifting add-ons, the storefront is built to support meaningful bridal personalization with clarity.</p>
                </div>
            </section>

            <section class="surface-card p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Craftsmanship story</p>
                        <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Bridal keepsakes with a curated rhythm</h2>
                    </div>
                    <a href="{{ route('collections.show', 'signature-nikah') }}" class="button-primary">Explore Signature Nikah</a>
                </div>
            </section>
        @elseif ($pageKind === 'contact')
            <section class="surface-card-featured p-8">
                <span class="eyebrow">Contact</span>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $page->title }}</h1>
                <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">{{ $page->body }}</p>
            </section>

            <section class="grid gap-6 md:grid-cols-2">
                <div class="surface-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">WhatsApp-first support</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ config('brand.contact.whatsapp') }}</h2>
                    <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">Best for order updates, personalization questions, and fulfillment clarifications.</p>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('brand.contact.whatsapp')) }}" class="button-primary mt-5">Call support</a>
                </div>
                <div class="surface-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Help shortcuts</p>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('faq.index') }}" class="button-ghost w-full justify-start">Open FAQ</a>
                        <a href="{{ route('orders.track.form') }}" class="button-ghost w-full justify-start">Track an order</a>
                        <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-ghost w-full justify-start">Browse bookings</a>
                    </div>
                </div>
            </section>

            <section class="surface-card p-8">
                <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Inquiry form</h2>
                <p class="mt-3 text-sm leading-8 text-[var(--color-text-soft)]">A direct messaging workflow is still preferred, but this layout reserves space for a premium inquiry form as the next support enhancement.</p>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Name</span>
                        <input type="text" class="field-input" disabled placeholder="Customer name">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Email</span>
                        <input type="email" class="field-input" disabled placeholder="name@example.com">
                    </label>
                    <label class="field-shell md:col-span-2">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Message</span>
                        <textarea class="field-textarea" rows="4" disabled placeholder="Message form UI reserved for a future support endpoint"></textarea>
                    </label>
                </div>
            </section>
        @elseif ($pageKind === 'policy')
            <section class="surface-card-featured p-8">
                <span class="eyebrow">Policy</span>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $page->title }}</h1>
                <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">{{ $page->meta_description ?: $page->body }}</p>
            </section>

            <section class="grid gap-6 lg:grid-cols-[220px_1fr]">
                <aside class="surface-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Policies</p>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('pages.show', 'shipping-policy') }}" class="block text-sm font-medium text-[var(--color-secondary-900)]">Shipping Policy</a>
                        <a href="{{ route('pages.show', 'return-policy') }}" class="block text-sm font-medium text-[var(--color-secondary-900)]">Return Policy</a>
                        <a href="{{ route('pages.show', 'privacy-policy') }}" class="block text-sm font-medium text-[var(--color-secondary-900)]">Privacy Policy</a>
                        <a href="{{ route('pages.show', 'terms-and-conditions') }}" class="block text-sm font-medium text-[var(--color-secondary-900)]">Terms & Conditions</a>
                    </div>
                </aside>

                <div class="space-y-6">
                    <section class="surface-card p-8">
                        <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Overview</h2>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">{{ $page->body }}</p>
                    </section>

                    <section class="surface-card p-8">
                        <div class="rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-8 text-[var(--color-secondary-900)]">
                            Important note: policy interpretation may vary depending on whether an order is standard, personalized, combo-based, or booking-related.
                        </div>
                    </section>
                </div>
            </section>
        @else
            <section class="surface-card p-8">
                <span class="eyebrow">Page</span>
                <h1 class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $page->title }}</h1>
                <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $page->body }}</p>
            </section>
        @endif
    </div>
</x-layouts.narrow>
