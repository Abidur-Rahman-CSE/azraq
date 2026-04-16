@props([
    'name',
    'type',
    'price',
    'description',
])

<article class="surface-card flex h-full flex-col p-6">
    <div class="flex items-start justify-between gap-4">
        <span class="eyebrow">{{ $type }}</span>
        <span class="text-sm font-medium text-[var(--color-primary-900)]">{{ $price }}</span>
    </div>

    <h3 class="mt-6 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $name }}</h3>
    <p class="mt-4 text-sm leading-7 text-[var(--color-text-soft)]">{{ $description }}</p>

    <div class="mt-auto pt-8">
        <a href="#" class="button-ghost">View product flow</a>
    </div>
</article>
