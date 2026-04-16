<x-layouts.narrow
    title="FAQ | Azraq Bridal"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq->answer,
                ],
            ])->values()->all(),
        ],
    ]"
>
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">FAQ</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Frequently asked questions</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">Find answers related to delivery, personalization, proof timing, returns, combos, and bookings without digging through dense text.</p>
        </section>

        @if (! empty($faqGroups))
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($faqGroups as $group)
                    <a href="#faq-{{ \Illuminate\Support\Str::slug($group['label']) }}" class="surface-card p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Category</p>
                        <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $group['label'] }}</h2>
                        <p class="mt-3 text-sm text-[var(--color-text-soft)]">{{ $group['items']->count() }} item{{ $group['items']->count() === 1 ? '' : 's' }}</p>
                    </a>
                @endforeach
            </section>
        @endif

        <div class="space-y-6">
            @foreach ($faqGroups as $group)
                <section id="faq-{{ \Illuminate\Support\Str::slug($group['label']) }}" class="surface-card p-8">
                    <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $group['label'] }}</h2>
                    <div class="mt-6 space-y-4">
                        @foreach ($group['items'] as $faq)
                            <details class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5" @open($loop->first)>
                                <summary class="cursor-pointer text-base font-semibold text-[var(--color-secondary-900)]">{{ $faq->question }}</summary>
                                <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">{{ $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.narrow>
