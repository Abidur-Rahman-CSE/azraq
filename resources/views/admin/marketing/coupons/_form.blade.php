@php($isEdit = $coupon->exists)
<form method="POST" action="{{ $isEdit ? route('admin.marketing.coupons.update', $coupon) : route('admin.marketing.coupons.store') }}" class="surface-card grid gap-6 p-6 md:grid-cols-2">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Code</span><input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
    <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Type</span><select name="type" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"><option value="fixed" @selected(old('type', $coupon->type)==='fixed')>Fixed</option><option value="percent" @selected(old('type', $coupon->type)==='percent')>Percent</option></select></label>
    <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Value</span><input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
    <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Minimum order amount</span><input type="number" step="0.01" name="minimum_order_amount" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
    <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)] md:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">Active</label>
    <div class="md:col-span-2"><button type="submit" class="button-primary">{{ $isEdit ? 'Save coupon' : 'Create coupon' }}</button></div>
</form>
