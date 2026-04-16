<x-layouts.admin title="Settings | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Settings</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Storefront settings</h2>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="surface-card grid gap-6 p-6 md:grid-cols-2">
            @csrf
            @method('PUT')
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
            <div class="md:col-span-2">
                <button type="submit" class="button-primary">Save settings</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
