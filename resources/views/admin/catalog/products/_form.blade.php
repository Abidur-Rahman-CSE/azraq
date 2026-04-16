@php($isEdit = $product->exists)
@php($selectedCollections = collect(old('collection_ids', $product->collections->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($selectedTags = collect(old('tag_ids', $product->tags->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($selectedRelated = collect(old('related_product_ids', $product->relatedProducts->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($variantRows = old('variants', $product->variants->map(fn ($variant) => [
    'name' => $variant->name,
    'sku' => $variant->sku,
    'option_values' => implode(', ', $variant->option_values ?? []),
    'price' => $variant->price,
    'compare_at_price' => $variant->compare_at_price,
    'stock_quantity' => $variant->stock_quantity,
    'is_default' => $variant->is_default,
])->all() ?: array_fill(0, 3, ['name' => '', 'sku' => '', 'option_values' => '', 'price' => '', 'compare_at_price' => '', 'stock_quantity' => '', 'is_default' => false])))
@php($bundleRows = old('bundle_items', $product->bundleItems->map(fn ($item) => [
    'child_product_id' => $item->child_product_id,
    'quantity' => $item->quantity,
])->all() ?: array_fill(0, 3, ['child_product_id' => '', 'quantity' => 1])))
@php($serviceMeta = old('service_meta', $product->serviceMeta?->toArray() ?? []))

<form method="POST" action="{{ $isEdit ? route('admin.catalog.products.update', $product) : route('admin.catalog.products.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Name</span>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            @error('name') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Category</span>
            <select name="category_id" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product type</span>
            <select name="type" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($productTypes as $type)
                    <option value="{{ $type['value'] }}" @selected(old('type', $product->type?->value ?? $product->type) === $type['value'])>{{ $type['label'] }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">SKU</span>
            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Status</span>
            <select name="status" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach (['draft', 'active', 'archived'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Price</span>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Compare at price</span>
            <input type="number" step="0.01" min="0" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Lead time (days)</span>
            <input type="number" min="0" name="lead_time_days" value="{{ old('lead_time_days', $product->lead_time_days ?? 0) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Stock quantity</span>
            <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Low stock threshold</span>
            <input type="number" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 0) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Excerpt</span>
            <textarea name="excerpt" rows="3" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ old('excerpt', $product->excerpt) }}</textarea>
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Description</span>
            <textarea name="description" rows="6" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ old('description', $product->description) }}</textarea>
        </label>
    </div>

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Collections</span>
            <select name="collection_ids[]" multiple class="min-h-36 w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($collections as $collection)
                    <option value="{{ $collection->id }}" @selected(in_array((string) $collection->id, $selectedCollections, true))>{{ $collection->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Tags</span>
            <select name="tag_ids[]" multiple class="min-h-36 w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTags, true))>{{ $tag->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Related products</span>
            <select name="related_product_ids[]" multiple class="min-h-36 w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($relatedProducts as $relatedProduct)
                    <option value="{{ $relatedProduct->id }}" @selected(in_array((string) $relatedProduct->id, $selectedRelated, true))>{{ $relatedProduct->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="manage_stock" value="0">
            <input type="checkbox" name="manage_stock" value="1" @checked(old('manage_stock', $product->manage_stock)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Manage stock
        </label>

        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Featured product
        </label>
    </div>

    <div class="surface-card p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Variants</h3>
                <p class="mt-2 text-sm text-[var(--color-text-soft)]">Phase 1 supports a compact variant structure with option labels stored as comma-separated values.</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            @foreach ($variantRows as $index => $variant)
                <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] p-4 md:grid-cols-6">
                    <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" placeholder="Variant name" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="SKU" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="text" name="variants[{{ $index }}][option_values]" value="{{ $variant['option_values'] ?? '' }}" placeholder="red, medium" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" step="0.01" min="0" name="variants[{{ $index }}][price]" value="{{ $variant['price'] ?? '' }}" placeholder="Price" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <input type="number" min="0" name="variants[{{ $index }}][stock_quantity]" value="{{ $variant['stock_quantity'] ?? '' }}" placeholder="Stock" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 text-sm text-[var(--color-secondary-900)]">
                        <input type="hidden" name="variants[{{ $index }}][is_default]" value="0">
                        <input type="checkbox" name="variants[{{ $index }}][is_default]" value="1" @checked($variant['is_default'] ?? false) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Default
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="surface-card p-6">
        <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Bundle items</h3>
        <p class="mt-2 text-sm text-[var(--color-text-soft)]">Use these rows when the product type is bundle/combo.</p>

        <div class="mt-6 space-y-4">
            @foreach ($bundleRows as $index => $bundleItem)
                <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] p-4 md:grid-cols-[1fr_180px]">
                    <select name="bundle_items[{{ $index }}][child_product_id]" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        <option value="">Select child product</option>
                        @foreach ($relatedProducts as $relatedProduct)
                            <option value="{{ $relatedProduct->id }}" @selected((string) ($bundleItem['child_product_id'] ?? '') === (string) $relatedProduct->id)>{{ $relatedProduct->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" min="1" name="bundle_items[{{ $index }}][quantity]" value="{{ $bundleItem['quantity'] ?? 1 }}" placeholder="Quantity" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </div>
            @endforeach
        </div>
    </div>

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <div class="md:col-span-2">
            <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Service metadata</h3>
            <p class="mt-2 text-sm text-[var(--color-text-soft)]">Use these fields when the product type is service/booking.</p>
        </div>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Service type</span>
            <input type="text" name="service_meta[service_type]" value="{{ $serviceMeta['service_type'] ?? '' }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Duration label</span>
            <input type="text" name="service_meta[duration_label]" value="{{ $serviceMeta['duration_label'] ?? '' }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Location scope</span>
            <input type="text" name="service_meta[location_scope]" value="{{ $serviceMeta['location_scope'] ?? '' }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Advance payment amount</span>
            <input type="number" step="0.01" min="0" name="service_meta[advance_payment_amount]" value="{{ $serviceMeta['advance_payment_amount'] ?? '' }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="service_meta[requires_advance_payment]" value="0">
            <input type="checkbox" name="service_meta[requires_advance_payment]" value="1" @checked((bool) ($serviceMeta['requires_advance_payment'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Requires advance payment
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Booking notes</span>
            <textarea name="service_meta[booking_notes]" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ $serviceMeta['booking_notes'] ?? '' }}</textarea>
        </label>
    </div>

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span>
            <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span>
            <input type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save changes' : 'Create product' }}</button>
        <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
