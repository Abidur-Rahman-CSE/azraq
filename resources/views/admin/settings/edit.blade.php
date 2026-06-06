@php
    $defaultPolicyRows = [
        ['label' => 'Timeline', 'value' => 'Prepared within {lead_time} to {lead_time_max} business days.'],
        ['label' => 'Packaging', 'value' => 'All items are gift-ready wrapped and carefully posted.'],
        ['label' => 'Care', 'value' => 'Keep prints dry, away from direct sunlight, and handle frames with clean hands.'],
        ['label' => 'Returns', 'value' => 'Personalized items are final sale once proof is approved; damaged parcels are reviewed quickly.'],
    ];

    $policyValue = old('default_shipping_care_policy', $settings['default_shipping_care_policy'] ?? null);
    $policyValue = filled($policyValue)
        ? $policyValue
        : json_encode($defaultPolicyRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

<x-layouts.admin title="Settings | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Settings</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Storefront settings</h2>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Announcement text</span>
                    <input type="text" name="announcement_text" value="{{ old('announcement_text', $settings['announcement_text'] ?? '') }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Announcement CTA label</span>
                    <input type="text" name="announcement_cta_label" value="{{ old('announcement_cta_label', $settings['announcement_cta_label'] ?? '') }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Announcement CTA href</span>
                    <input type="text" name="announcement_cta_href" value="{{ old('announcement_cta_href', $settings['announcement_cta_href'] ?? '') }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Support phone</span>
                    <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone'] ?? '') }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Support email</span>
                    <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? '') }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
            </section>

            <section id="shipping-care-policy-settings" class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Shipping, care, and policy</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Default product policy rows</h3>
                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Products use these rows unless a product-specific override is saved. Use <code>{lead_time}</code> and <code>{lead_time_max}</code> for product delivery windows.</p>
                </div>

                <label class="mt-5 block space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Policy JSON</span>
                    <textarea name="default_shipping_care_policy" rows="12" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 font-mono text-sm">{{ $policyValue }}</textarea>
                    @error('default_shipping_care_policy') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>
            </section>

            <div>
                <button type="submit" class="button-primary">Save settings</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
