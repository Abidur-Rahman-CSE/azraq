<x-layouts.admin
    title="Admin Dashboard | Azraq Bridal"
    page-title="Dashboard"
    page-subtitle="Azraq Bridal admin foundation"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Dashboard'],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Operational overview"
            title="Premium control panel for catalog, proofing, and content."
            description="Catalog admin foundation, now upgraded into a premium control panel for product media, Nikah template tooling, mockup mapping, and proof-aware order operations. The cards below surface the highest-priority gaps first so the next implementation phases stay focused."
        >
            <x-slot:actions>
                @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="{{ $loop->first ? 'button-primary' : 'button-ghost' }}">{{ $action['label'] }}</a>
                @endforeach
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($kpis as $kpi)
                <x-admin.kpi-card :label="$kpi['label']" :value="$kpi['value']" :description="$kpi['description']" />
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-4">
            @foreach ($alerts as $alert)
                <article class="admin-alert-card surface-card {{ 'admin-alert-card--'.$alert['tone'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $alert['label'] }}</p>
                            <p class="admin-alert-card__value">{{ number_format($alert['value']) }}</p>
                        </div>
                        <span class="eyebrow !px-3 !py-2 !text-[0.65rem]">{{ $alert['tone'] === 'success' ? 'Ready' : 'Needs work' }}</span>
                    </div>
                    <p class="text-sm leading-7 text-[var(--color-text-soft)]">{{ $alert['description'] }}</p>
                    <a href="{{ $alert['href'] }}" class="inline-flex text-sm font-semibold text-[var(--color-secondary-900)]">{{ $alert['action'] }}</a>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="surface-card overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-b border-[var(--color-border-soft)] px-6 py-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Orders</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Recent orders</h3>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="button-ghost">View all</a>
                </div>

                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                                <tr>
                                    <th class="px-6 py-4 font-medium">Order</th>
                                    <th class="px-6 py-4 font-medium">Customer</th>
                                    <th class="px-6 py-4 font-medium">Total</th>
                                    <th class="px-6 py-4 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-[var(--color-secondary-900)]">{{ $order->order_number }}</a>
                                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $order->created_at?->format('d M Y, h:i A') }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-[var(--color-secondary-900)]">{{ $order->customer_name }}</p>
                                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $order->customer_email }}</p>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $order->total_amount, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">
                                                {{ ucfirst($order->fulfillment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="admin-empty-state">
                        <p class="text-lg font-semibold text-[var(--color-secondary-900)]">No orders yet</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">As transactions start coming in, this panel will show proof-heavy orders first so support can act quickly.</p>
                    </div>
                @endif
            </div>

            <div class="grid gap-6">
                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Catalog health</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Quick stats</h3>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach ($categoryStats as $stat)
                            <article class="surface-card-soft p-5">
                                <p class="text-sm text-[var(--color-text-soft)]">{{ $stat['label'] }}</p>
                                <p class="mt-3 text-3xl font-semibold text-[var(--color-secondary-900)]">{{ number_format($stat['value']) }}</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $stat['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="surface-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Merchandising</p>
                            <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Top products</h3>
                        </div>
                        <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Products</a>
                    </div>

                    @if ($topProducts->isNotEmpty())
                        <div class="mt-5 space-y-3">
                            @foreach ($topProducts as $product)
                                <article class="surface-card-soft flex items-center justify-between gap-4 p-4">
                                    <div>
                                        <p class="font-semibold text-[var(--color-secondary-900)]">{{ $product->product_name }}</p>
                                        <p class="mt-1 text-sm text-[var(--color-text-soft)]">{{ number_format((int) $product->units_sold) }} units sold</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $product->revenue, 2) }}</p>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="admin-empty-state !px-0 !pb-0">
                            <p class="text-sm leading-7 text-[var(--color-text-soft)]">Top product insights will appear after the first real orders are placed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-layouts.admin>
