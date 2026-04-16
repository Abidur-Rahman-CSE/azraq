<x-layouts.narrow title="Booking Request Sent | Azraq Bridal">
    <div class="space-y-6">
        <div class="surface-card p-8">
            <span class="eyebrow">Booking request sent</span>
            <h1 class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">Your service request is in.</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">Booking number: <span class="font-semibold text-[var(--color-secondary-900)]">{{ $booking->booking_number }}</span></p>
            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">We’ll review your preferred schedule, package details, and location before confirming the next step.</p>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $booking->product?->name }}</h2>
            <div class="mt-5 space-y-3 text-sm text-[var(--color-text-soft)]">
                <p>Preferred date: {{ $booking->preferred_date?->format('M d, Y') }}</p>
                <p>Preferred time: {{ $booking->preferred_time }}</p>
                <p>Area: {{ $booking->location_area }}</p>
                <p>Status: {{ ucfirst($booking->status) }}</p>
                <p>Deposit: {{ ucfirst(str_replace('_', ' ', $booking->deposit_status)) }}</p>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('bookings.index') }}" class="button-primary">View recent booking requests</a>
                <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-ghost">Browse service offerings</a>
            </div>
        </div>
    </div>
</x-layouts.narrow>
