<x-layouts.admin :title="'Manage Order '.$order->order_number">
    <div class="space-y-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Orders</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">{{ $order->order_number }}</h2>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $order->customer_name }} · {{ $order->customer_email }} · {{ $order->customer_phone }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="button-ghost">Back to orders</a>
        </div>

        <div class="grid gap-8 xl:grid-cols-[1fr_420px]">
            <div class="space-y-8">
                <div class="surface-card overflow-hidden">
                    <div class="border-b border-[var(--color-border-soft)] px-6 py-5">
                        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Items</h3>
                    </div>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                            <tr>
                                <th class="px-6 py-4 font-medium">Item</th>
                                <th class="px-6 py-4 font-medium">Type</th>
                                <th class="px-6 py-4 font-medium">Qty</th>
                                <th class="px-6 py-4 font-medium">Personalization</th>
                                <th class="px-6 py-4 font-medium">Subtotal</th>
                                <th class="px-6 py-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[var(--color-secondary-900)]">{{ $item->product_name }}</p>
                                        @if ($item->line_item_meta['variant_name'] ?? null)
                                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">Variant: {{ $item->line_item_meta['variant_name'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ str($item->product_type)->headline() }}</td>
                                    <td class="px-6 py-4">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $item->personalization_status)) }}</td>
                                    <td class="px-6 py-4">BDT {{ number_format((float) $item->subtotal_amount, 0) }}</td>
                                    <td class="px-6 py-4">
                                        @if (($item->line_item_meta['personalization'] ?? []) !== [])
                                            <a href="{{ route('admin.orders.personalization.show', [$order, $item]) }}" class="button-ghost">Review proof</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="surface-card p-6">
                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Order timeline</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($order->events as $event)
                            <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/70 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-[var(--color-secondary-900)]">{{ $event->message }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $event->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <span class="text-xs font-medium uppercase tracking-[0.14em] text-[var(--color-primary-900)]">{{ str($event->event_type)->headline() }}</span>
                                </div>
                                @if ($event->meta)
                                    <pre class="mt-3 overflow-x-auto rounded-[var(--radius-md)] bg-[var(--color-surface-cream)] p-3 text-xs text-[var(--color-text-soft)]">{{ json_encode($event->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[var(--color-text-soft)]">No timeline events yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="surface-card space-y-5 p-6">
                    @csrf
                    @method('PUT')
                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Update statuses</h3>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Payment status</span>
                        <select name="payment_status" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach (['pending', 'unpaid', 'paid', 'failed', 'refunded'] as $status)
                                <option value="{{ $status }}" @selected($order->payment_status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Fulfillment status</span>
                        <select name="fulfillment_status" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach (['pending', 'processing', 'fulfilled', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected($order->fulfillment_status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Shipping status</span>
                        <select name="shipping_status" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach (['not_shipped', 'packed', 'in_transit', 'delivered', 'returned'] as $status)
                                <option value="{{ $status }}" @selected($order->shipping_status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Timeline note</span>
                        <textarea name="note" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></textarea>
                    </label>

                    <button type="submit" class="button-primary">Save status update</button>
                </form>

                <div class="surface-card p-6">
                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Address summary</h3>
                    <div class="mt-5 space-y-4 text-sm text-[var(--color-text-soft)]">
                        <div>
                            <p class="font-medium text-[var(--color-secondary-900)]">Shipping</p>
                            <p class="mt-2">{{ $order->shipping_address['line_1'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['line_2'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['area'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['country'] ?? '' }}</p>
                        </div>
                        <div class="border-t border-[var(--color-border-soft)] pt-4">
                            <p class="font-medium text-[var(--color-secondary-900)]">Billing</p>
                            <p class="mt-2">{{ $order->billing_address['line_1'] ?? '' }}</p>
                            <p>{{ $order->billing_address['line_2'] ?? '' }}</p>
                            <p>{{ $order->billing_address['area'] ?? '' }}, {{ $order->billing_address['city'] ?? '' }}</p>
                            <p>{{ $order->billing_address['country'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
