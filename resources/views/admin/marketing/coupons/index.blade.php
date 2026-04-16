<x-layouts.admin title="Coupons | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Marketing</p><h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Coupons</h2></div>
            <a href="{{ route('admin.marketing.coupons.create') }}" class="button-primary">New coupon</a>
        </div>
        <div class="grid gap-4">
            @foreach ($coupons as $coupon)
                <article class="surface-card flex items-center justify-between gap-4 p-6">
                    <div><h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $coupon->code }}</h3><p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ ucfirst($coupon->type) }} · {{ $coupon->value }} · Min BDT {{ $coupon->minimum_order_amount }}</p></div>
                    <a href="{{ route('admin.marketing.coupons.edit', $coupon) }}" class="button-ghost">Edit</a>
                </article>
            @endforeach
        </div>
        {{ $coupons->links() }}
    </div>
</x-layouts.admin>
