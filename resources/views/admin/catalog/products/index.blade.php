<x-layouts.admin title="Products | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Products</h2>
            </div>
            <a href="{{ route('admin.catalog.products.create') }}" class="button-primary">New product</a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($productTypes as $type)
                <article class="surface-card p-5">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-primary-900)]">{{ $type['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold text-[var(--color-secondary-900)]">{{ $products->getCollection()->filter(fn ($product) => $product->type?->value === $type['value'])->count() }}</p>
                    <p class="mt-2 text-sm text-[var(--color-text-soft)]">In this page of the catalog list.</p>
                </article>
            @endforeach
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Product</th>
                        <th class="px-6 py-4 font-medium">Type</th>
                        <th class="px-6 py-4 font-medium">Category</th>
                        <th class="px-6 py-4 font-medium">Price</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $product->sku ?: 'No SKU' }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $product->type?->label() }}</td>
                            <td class="px-6 py-4">{{ $product->category?->name }}</td>
                            <td class="px-6 py-4">BDT {{ number_format((float) $product->price, 2) }}</td>
                            <td class="px-6 py-4">{{ ucfirst($product->status) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.catalog.products.edit', $product) }}" class="text-[var(--color-secondary-900)]">Edit</a>
                                    <form method="POST" action="{{ route('admin.catalog.products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[var(--color-danger)]">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No products yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</x-layouts.admin>
