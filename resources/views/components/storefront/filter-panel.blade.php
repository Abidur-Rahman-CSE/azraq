@props([
    'filters',
    'action',
])

<form method="GET" action="{{ $action }}" class="surface-sidebar p-6">
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold text-[var(--text-main)]">Search</p>
            <input
                type="text"
                name="search"
                value="{{ $filters['applied']['search'] }}"
                placeholder="Search products"
                class="field-input mt-3"
            >
        </div>

        <div>
            <p class="text-sm font-semibold text-[var(--text-main)]">Product type</p>
            <select name="type" class="field-select mt-3">
                <option value="">All product types</option>
                @foreach ($filters['productTypes'] as $type)
                    <option value="{{ $type['value'] }}" @selected($filters['applied']['type'] === $type['value'])>{{ $type['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <p class="text-sm font-semibold text-[var(--text-main)]">Tag</p>
            <select name="tag" class="field-select mt-3">
                <option value="">All tags</option>
                @foreach ($filters['tags'] as $tag)
                    <option value="{{ $tag->slug }}" @selected($filters['applied']['tag'] === $tag->slug)>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <p class="text-sm font-semibold text-[var(--text-main)]">Sort</p>
            <select name="sort" class="field-select mt-3">
                <option value="">Newest first</option>
                <option value="price_low" @selected($filters['applied']['sort'] === 'price_low')>Price: low to high</option>
                <option value="price_high" @selected($filters['applied']['sort'] === 'price_high')>Price: high to low</option>
                <option value="name" @selected($filters['applied']['sort'] === 'name')>Name</option>
            </select>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="button-primary">Apply filters</button>
            <a href="{{ $action }}" class="button-ghost">Reset</a>
        </div>
    </div>
</form>
