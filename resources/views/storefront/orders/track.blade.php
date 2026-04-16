<x-layouts.narrow title="Track Order | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8 text-center">
            <span class="eyebrow">Track order</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Find your order in one step</h1>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-[var(--color-text-soft)]">Enter your order number and email address to review payment, fulfillment, and shipping progress without needing an account.</p>
        </section>

        <form method="POST" action="{{ route('orders.track') }}" class="surface-card space-y-6 p-8">
            @csrf
            <label class="field-shell">
                <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Order number</span>
                <input type="text" name="order_number" value="{{ old('order_number') }}" class="field-input">
            </label>
            <label class="field-shell">
                <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Email address</span>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="field-input">
            </label>

            <div class="rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                Delivery help: use the exact email used at checkout. If you still need support, contact Azraq via WhatsApp for a faster manual lookup.
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="button-primary">Track order</button>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('brand.contact.whatsapp')) }}" class="button-ghost">Get support</a>
            </div>
        </form>
    </div>
</x-layouts.narrow>
