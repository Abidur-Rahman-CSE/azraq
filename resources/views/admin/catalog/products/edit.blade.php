<x-layouts.admin title="Edit Product | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Edit product</h2>
            </div>
            <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Back to products</a>
        </div>

        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
