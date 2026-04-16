<x-layouts.admin title="Create Product | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Catalog</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Create product</h2>
        </div>

        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
