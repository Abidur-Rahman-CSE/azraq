<x-layouts.admin title="Orders | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Operations</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Order workflow management</h2>
            </div>
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Order</th>
                        <th class="px-6 py-4 font-medium">Customer</th>
                        <th class="px-6 py-4 font-medium">Payment</th>
                        <th class="px-6 py-4 font-medium">Fulfillment</th>
                        <th class="px-6 py-4 font-medium">Shipping</th>
                        <th class="px-6 py-4 font-medium">Total</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $order->order_number }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $order->items_count }} items</p>
                            </td>
                            <td class="px-6 py-4">
                                <p>{{ $order->customer_name }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $order->customer_email }}</p>
                            </td>
                            <td class="px-6 py-4">{{ ucfirst($order->payment_status) }}</td>
                            <td class="px-6 py-4">{{ ucfirst($order->fulfillment_status) }}</td>
                            <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</td>
                            <td class="px-6 py-4">BDT {{ number_format((float) $order->total_amount, 0) }}</td>
                            <td class="px-6 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-[var(--color-secondary-900)]">Manage</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-layouts.admin>
