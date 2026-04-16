<x-layouts.admin title="Edit Personalization Template | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Personalization</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Edit template</h2>
            </div>
            @if ($template->product)
                <a href="{{ route('products.show', $template->product) }}" class="button-ghost">Open storefront view</a>
            @endif
        </div>

        @include('admin.personalization.templates._form')
    </div>
</x-layouts.admin>
