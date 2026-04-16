<x-layouts.admin title="FAQs | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p><h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">FAQs</h2></div>
            <a href="{{ route('admin.content.faqs.create') }}" class="button-primary">New FAQ</a>
        </div>
        <div class="grid gap-4">
            @foreach ($faqs as $faq)
                <article class="surface-card flex items-center justify-between gap-4 p-6">
                    <div>
                        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $faq->question }}</h3>
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $faq->answer }}</p>
                    </div>
                    <a href="{{ route('admin.content.faqs.edit', $faq) }}" class="button-ghost">Edit</a>
                </article>
            @endforeach
        </div>
        {{ $faqs->links() }}
    </div>
</x-layouts.admin>
