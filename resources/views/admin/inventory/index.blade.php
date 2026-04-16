<x-layouts.admin title="Inventory | Azraq Bridal">
    <div class="space-y-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Operations</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Inventory overview</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--color-text-soft)]">Track low-stock items, review recent inventory changes, and apply manual adjustments without touching service-led products.</p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="surface-card p-6">
                <p class="text-sm text-[var(--color-text-soft)]">Stock-managed products</p>
                <p class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $products->count() }}</p>
            </article>
            <article class="surface-card p-6">
                <p class="text-sm text-[var(--color-text-soft)]">Low-stock products</p>
                <p class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $lowStockProducts->count() }}</p>
            </article>
            <article class="surface-card p-6">
                <p class="text-sm text-[var(--color-text-soft)]">Low-stock variants</p>
                <p class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $lowStockVariants->count() }}</p>
            </article>
            <article class="surface-card p-6">
                <p class="text-sm text-[var(--color-text-soft)]">Recent movements</p>
                <p class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $movements->total() }}</p>
            </article>
        </div>

        <div class="grid gap-8 xl:grid-cols-[1fr_380px]">
            <div class="space-y-8">
                <div class="surface-card overflow-hidden">
                    <div class="border-b border-[var(--color-border-soft)] px-6 py-5">
                        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Low-stock product alerts</h3>
                    </div>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                            <tr>
                                <th class="px-6 py-4 font-medium">Product</th>
                                <th class="px-6 py-4 font-medium">Current stock</th>
                                <th class="px-6 py-4 font-medium">Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $product)
                                <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[var(--color-secondary-900)]">{{ $product->name }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $product->sku }}</p>
                                    </td>
                                    <td class="px-6 py-4">{{ $product->stock_quantity }}</td>
                                    <td class="px-6 py-4">{{ $product->low_stock_threshold }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No low-stock product alerts right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="surface-card overflow-hidden">
                    <div class="border-b border-[var(--color-border-soft)] px-6 py-5">
                        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Recent stock movements</h3>
                    </div>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                            <tr>
                                <th class="px-6 py-4 font-medium">Target</th>
                                <th class="px-6 py-4 font-medium">Type</th>
                                <th class="px-6 py-4 font-medium">Change</th>
                                <th class="px-6 py-4 font-medium">After</th>
                                <th class="px-6 py-4 font-medium">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[var(--color-secondary-900)]">{{ $movement->product?->name }}</p>
                                        @if ($movement->variant)
                                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">Variant: {{ $movement->variant->name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ str($movement->type)->headline() }}</td>
                                    <td class="px-6 py-4">{{ $movement->quantity_change }}</td>
                                    <td class="px-6 py-4">{{ $movement->quantity_after }}</td>
                                    <td class="px-6 py-4 text-[var(--color-text-soft)]">{{ $movement->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No inventory movements yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $movements->links() }}
            </div>

            <div class="surface-card p-6">
                <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Manual adjustment</h3>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Use positive quantities for restocks and negative quantities for corrections or damages.</p>

                <form method="POST" action="{{ route('admin.inventory.adjustments.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Target type</span>
                        <select name="target" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <option value="product">Product stock</option>
                            <option value="variant">Variant stock</option>
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product</span>
                        <select name="product_id" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Variant ID</span>
                        <input type="number" name="variant_id" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Optional variant ID">
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Quantity change</span>
                        <input type="number" name="quantity_change" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="-2 or 10">
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Notes</span>
                        <textarea name="notes" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></textarea>
                    </label>

                    <button type="submit" class="button-primary">Apply adjustment</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
