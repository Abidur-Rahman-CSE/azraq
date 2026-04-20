@php($isEdit = $collection->exists)
@php($selectedProducts = collect(old('product_ids', $collection->products->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())

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
                            <span class="text-xs text-[var(--color-text-soft)]">Recommended for collection cards, shop spotlights, and homepage collection sections.</span>
                            @error('cover_image_upload') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>
            </div>

            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">3. Product assignment</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Curated product set</h3>
                </div>

                <label class="field-shell mt-6">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Assigned products</span>
                    <select name="product_ids[]" multiple class="field-select min-h-56">
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(in_array((string) $product->id, $selectedProducts, true))>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs text-[var(--color-text-soft)]">Manual mode uses explicit product assignment. Automatic mode is visually supported here now and can be extended to rules later.</span>
                </label>
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
