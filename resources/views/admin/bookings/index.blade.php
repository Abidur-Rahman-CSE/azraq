<x-layouts.admin title="Booking Requests | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Operations</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Booking requests</h2>
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Booking</th>
                        <th class="px-6 py-4 font-medium">Service</th>
                        <th class="px-6 py-4 font-medium">Requested slot</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium">Deposit</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $booking->booking_number }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $booking->customer_name }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $booking->product?->name }}</td>
                            <td class="px-6 py-4">{{ $booking->preferred_date?->format('M d, Y') }} · {{ $booking->preferred_time }}</td>
                            <td class="px-6 py-4">{{ ucfirst($booking->status) }}</td>
                            <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $booking->deposit_status)) }}</td>
                            <td class="px-6 py-4"><a href="{{ route('admin.bookings.show', $booking) }}" class="text-[var(--color-secondary-900)]">Manage</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No booking requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $bookings->links() }}
    </div>
</x-layouts.admin>
