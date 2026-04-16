<x-layouts.narrow title="Booking Requests | Azraq Bridal">
    <div class="space-y-6">
        <div class="surface-card p-8">
            <span class="eyebrow">Booking requests</span>
            <h1 class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">Recent service requests on this browser session</h1>
        </div>

        <div class="space-y-4">
            @forelse ($bookings as $booking)
                <article class="surface-card p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $booking->booking_number }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $booking->product?->name }}</h2>
                            <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $booking->preferred_date?->format('M d, Y') }} · {{ $booking->preferred_time }} · {{ $booking->location_area }}</p>
                        </div>
                        <div class="text-right text-sm text-[var(--color-text-soft)]">
                            <p>Status: {{ ucfirst($booking->status) }}</p>
                            <p class="mt-1">Deposit: {{ ucfirst(str_replace('_', ' ', $booking->deposit_status)) }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="surface-card p-8">
                    <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">No booking requests yet.</h2>
                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-soft)]">Submit a bridal, non-bridal, or mehendi service request and it will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.narrow>
