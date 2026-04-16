<x-layouts.admin :title="'Manage Booking '.$booking->booking_number">
    <div class="space-y-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Bookings</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">{{ $booking->booking_number }}</h2>
                <p class="mt-3 text-sm text-[var(--color-text-soft)]">{{ $booking->customer_name }} · {{ $booking->customer_email }} · {{ $booking->customer_phone }}</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="button-ghost">Back to bookings</a>
        </div>

        <div class="grid gap-8 xl:grid-cols-[1fr_420px]">
            <div class="space-y-8">
                <div class="surface-card p-6">
                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Request details</h3>
                    <div class="mt-5 space-y-3 text-sm text-[var(--color-text-soft)]">
                        <p>Service: {{ $booking->product?->name }}</p>
                        <p>Preferred date: {{ $booking->preferred_date?->format('M d, Y') }}</p>
                        <p>Preferred time: {{ $booking->preferred_time }}</p>
                        <p>Location area: {{ $booking->location_area }}</p>
                        <p>Package details: {{ $booking->package_details ?: '—' }}</p>
                        <p>Notes: {{ $booking->notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="surface-card space-y-5 p-6">
                @csrf
                @method('PUT')
                <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Update booking</h3>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Status</span>
                    <select name="status" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        @foreach (['pending', 'contacted', 'confirmed', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Deposit status</span>
                    <select name="deposit_status" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        @foreach (['not_required', 'pending', 'paid', 'waived'] as $status)
                            <option value="{{ $status }}" @selected($booking->deposit_status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Admin note</span>
                    <textarea name="notes" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></textarea>
                </label>

                <button type="submit" class="button-primary">Save booking update</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
