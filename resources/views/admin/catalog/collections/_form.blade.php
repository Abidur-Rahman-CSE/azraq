@php
    $isEdit = $collection->exists;
    $selectedProducts = collect(old('product_ids', $collection->products->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $fallbackProductImage = asset('images/logo/Azraq.svg');
    $productPickerItems = $products->map(function ($product) use ($fallbackProductImage) {
        return [
            'id' => (string) $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'type' => $product->type?->label() ?? 'Product',
            'status' => ucfirst((string) $product->status),
            'price' => 'BDT '.number_format((float) $product->price),
            'image' => $product->storefront_preview_image_url ?: $product->featured_image_url ?: $product->primaryImage()?->image_url ?: $fallbackProductImage,
        ];
    })->values()->all();
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('admin.catalog.collections.update', $collection) : route('admin.catalog.collections.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">1. Collection information</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Core setup</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Collection name</span>
                    <input type="text" name="name" value="{{ old('name', $collection->name) }}" class="field-input">
                    @error('name') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $collection->slug) }}" class="field-input" placeholder="Auto-generated if left blank">
                    @error('slug') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Description</span>
                    <textarea name="description" rows="5" class="field-textarea">{{ old('description', $collection->description) }}</textarea>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Collection mode</span>
                    <select name="collection_mode" class="field-select">
                        <option value="manual" @selected(old('collection_mode', $collection->collection_mode) === 'manual')>Manual</option>
                        <option value="automatic" @selected(old('collection_mode', $collection->collection_mode) === 'automatic')>Automatic</option>
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $collection->sort_order ?? 0) }}" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">CTA label</span>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $collection->cta_label) }}" class="field-input" placeholder="Optional CTA label for spotlight sections">
                </label>
            </div>

            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">2. Cover image</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Collection media</h3>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Current cover</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                            @if ($collection->cover_image_url)
                                <img src="{{ $collection->cover_image_url }}" alt="{{ $collection->name }}" class="aspect-[4/3] w-full object-cover">
                            @else
                                <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">No cover uploaded yet.</div>
                            @endif
                        </div>
                    </div>

                    <div class="surface-card-soft p-5">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload cover image</span>
                            <input type="file" name="cover_image_upload" accept="image/*" class="field-input">
                            <span class="text-xs text-[var(--color-text-soft)]">Recommended for collection cards, shop spotlights, and homepage collection sections. JPG, PNG, or WEBP works best. Max 10MB.</span>
                            @error('cover_image_upload') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>
            </div>

            <div
                class="surface-card p-6"
                x-data="{
                    open: false,
                    query: '',
                    type: '',
                    category: '',
                    selected: @js($selectedProducts),
                    products: @js($productPickerItems),
                    get selectedProducts() {
                        return this.selected
                            .map((id) => this.products.find((product) => product.id === String(id)))
                            .filter(Boolean);
                    },
                    get categories() {
                        return [...new Set(this.products.map((product) => product.category).filter(Boolean))].sort();
                    },
                    get types() {
                        return [...new Set(this.products.map((product) => product.type).filter(Boolean))].sort();
                    },
                    get filteredProducts() {
                        const term = this.query.trim().toLowerCase();

                        return this.products.filter((product) => {
                            const matchesTerm = ! term || [product.name, product.sku, product.category, product.type, product.status]
                                .filter(Boolean)
                                .some((value) => String(value).toLowerCase().includes(term));
                            const matchesType = ! this.type || product.type === this.type;
                            const matchesCategory = ! this.category || product.category === this.category;

                            return matchesTerm && matchesType && matchesCategory;
                        });
                    },
                    isSelected(id) {
                        return this.selected.includes(String(id));
                    },
                    toggle(id) {
                        id = String(id);
                        this.selected = this.isSelected(id)
                            ? this.selected.filter((selectedId) => selectedId !== id)
                            : [...this.selected, id];
                    },
                    remove(id) {
                        this.selected = this.selected.filter((selectedId) => selectedId !== String(id));
                    },
                    clearFilters() {
                        this.query = '';
                        this.type = '';
                        this.category = '';
                    },
                }"
            >
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">3. Product assignment</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Curated product set</h3>
                </div>

                <template x-for="productId in selected" :key="`selected-input-${productId}`">
                    <input type="hidden" name="product_ids[]" :value="productId">
                </template>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Assigned products</p>
                        <p class="mt-1 text-xs text-[var(--color-text-soft)]">Manual mode uses explicit product assignment. Automatic mode is visually supported here now and can be extended to rules later.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[var(--color-neutral-100)] px-4 py-2 text-sm font-semibold text-[var(--color-secondary-900)]" x-text="`${selected.length} selected`"></span>
                        <button type="button" class="button-ghost" @click="open = true">Choose products</button>
                    </div>
                </div>

                @error('product_ids') <p class="mt-3 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror
                @error('product_ids.*') <p class="mt-3 text-xs text-[var(--color-danger)]">{{ $message }}</p> @enderror

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <template x-if="selectedProducts.length === 0">
                        <div class="rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-soft)] bg-white/70 px-5 py-8 text-center text-sm text-[var(--color-text-soft)] sm:col-span-2">
                            No products selected yet.
                        </div>
                    </template>

                    <template x-for="product in selectedProducts" :key="`selected-product-${product.id}`">
                        <div class="flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/85 p-3 shadow-sm">
                            <img :src="product.image" :alt="product.name" class="h-16 w-16 shrink-0 rounded-[var(--radius-lg)] border border-[var(--color-border-soft)] object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-[var(--color-secondary-900)]" x-text="product.name"></p>
                                <p class="truncate text-xs text-[var(--color-text-soft)]" x-text="[product.category, product.type].filter(Boolean).join(' · ')"></p>
                                <p class="text-xs font-semibold text-[var(--color-primary-900)]" x-text="product.price"></p>
                            </div>
                            <button type="button" class="rounded-full border border-[var(--color-border-soft)] px-3 py-2 text-xs font-semibold text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-white" @click="remove(product.id)">
                                Remove
                            </button>
                        </div>
                    </template>
                </div>

                <template x-teleport="body">
                    <div
                        x-cloak
                        x-show="open"
                        x-transition.opacity
                        class="collection-product-picker-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-label="Choose collection products"
                        @keydown.escape.window="open = false"
                    >
                        <button type="button" class="collection-product-picker-modal__backdrop" aria-label="Close product picker" @click="open = false"></button>

                        <div class="collection-product-picker-modal__panel">
                            <div class="collection-product-picker-modal__header">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Product picker</p>
                                    <h3 class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">Choose products for this collection</h3>
                                    <p class="mt-1 text-sm text-[var(--color-text-soft)]" x-text="`${filteredProducts.length} matching products · ${selected.length} selected`"></p>
                                </div>

                                <button type="button" class="button-ghost" @click="open = false">Done</button>
                            </div>

                            <div class="grid gap-3 border-b border-[var(--color-border-soft)] p-4 lg:grid-cols-[1fr_220px_220px_auto]">
                                <input type="search" class="field-input" placeholder="Search by name, SKU, category, or type" x-model.debounce.150ms="query">

                                <select class="field-select" x-model="category">
                                    <option value="">All categories</option>
                                    <template x-for="categoryName in categories" :key="categoryName">
                                        <option :value="categoryName" x-text="categoryName"></option>
                                    </template>
                                </select>

                                <select class="field-select" x-model="type">
                                    <option value="">All product types</option>
                                    <template x-for="typeName in types" :key="typeName">
                                        <option :value="typeName" x-text="typeName"></option>
                                    </template>
                                </select>

                                <button type="button" class="button-ghost" @click="clearFilters()">Clear filters</button>
                            </div>

                            <div class="max-h-[62vh] overflow-y-auto p-4">
                                <template x-if="filteredProducts.length === 0">
                                    <div class="rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-soft)] bg-white px-6 py-10 text-center text-sm text-[var(--color-text-soft)]">
                                        No products match these filters.
                                    </div>
                                </template>

                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                    <template x-for="product in filteredProducts" :key="`picker-product-${product.id}`">
                                        <button
                                            type="button"
                                            class="group flex h-full gap-3 rounded-[var(--radius-xl)] border bg-white p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                                            :class="isSelected(product.id) ? 'border-[var(--color-primary-900)] ring-2 ring-[var(--color-primary-900)]/15' : 'border-[var(--color-border-soft)]'"
                                            @click="toggle(product.id)"
                                        >
                                            <img :src="product.image" :alt="product.name" class="h-24 w-24 shrink-0 rounded-[var(--radius-lg)] border border-[var(--color-border-soft)] object-cover">
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-start justify-between gap-2">
                                                    <span class="line-clamp-2 text-sm font-semibold text-[var(--color-secondary-900)]" x-text="product.name"></span>
                                                    <span class="mt-1 h-4 w-4 shrink-0 rounded-full border" :class="isSelected(product.id) ? 'border-[var(--color-primary-900)] bg-[var(--color-primary-900)]' : 'border-[var(--color-border-soft)] bg-white'"></span>
                                                </span>
                                                <span class="mt-2 block text-xs text-[var(--color-text-soft)]" x-text="product.sku || 'No SKU'"></span>
                                                <span class="mt-1 block text-xs text-[var(--color-text-soft)]" x-text="[product.category, product.type].filter(Boolean).join(' · ')"></span>
                                                <span class="mt-3 inline-flex rounded-full bg-[var(--color-neutral-100)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]" x-text="product.price"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">4. SEO metadata</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Search and sharing</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $collection->meta_title) }}" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $collection->meta_description) }}" class="field-input">
                </label>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-card p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Publish settings</p>
                <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Merchandising controls</h3>

                <div class="mt-6 space-y-4">
                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $collection->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Active collection
                    </label>

                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $collection->is_featured)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Feature this collection in storefront merchandising
                    </label>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Collection preview</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                            @if ($collection->cover_image_url)
                                <img src="{{ $collection->cover_image_url }}" alt="{{ $collection->name }}" class="aspect-[4/3] w-full object-cover">
                            @else
                                <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Cover preview appears once the collection image is saved.</div>
                            @endif
                        </div>
                        <p class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]">{{ old('name', $collection->name ?: 'Collection title preview') }}</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ old('description', $collection->description ?: 'Collection description preview for storefront spotlights.') }}</p>
                    </div>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Checklist</p>
                        <div class="mt-4 space-y-3 text-sm text-[var(--color-text-soft)]">
                            <p>{{ $collection->cover_image_url ? 'Cover image is ready.' : 'Cover image still needed.' }}</p>
                            <p>{{ $collection->products->count() > 0 ? 'Assigned product set exists.' : 'No products assigned yet.' }}</p>
                            <p>{{ $collection->is_featured ? 'Featured state is enabled.' : 'Featured state is disabled.' }}</p>
                            <p>{{ $collection->cta_label ? 'Custom CTA label is set.' : 'Using default storefront CTA text.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save collection changes' : 'Create collection' }}</button>
        <a href="{{ route('admin.catalog.collections.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
