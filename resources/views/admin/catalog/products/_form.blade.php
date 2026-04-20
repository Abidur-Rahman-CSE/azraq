@php($isEdit = $product->exists)
@php($selectedCollections = collect(old('collection_ids', $product->collections->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($selectedTags = collect(old('tag_ids', $product->tags->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($selectedRelated = collect(old('related_product_ids', $product->relatedProducts->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($selectedRelatedCategories = collect(old('related_category_ids', $product->relatedCategories->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
@php($existingImages = $product->images->sortBy('position')->values())
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
@php($currentType = old('type', $product->type?->value ?? $product->type))
@php($selectedTemplateId = old('assigned_template_id', $product->personalizationTemplate?->id))
@php($selectedMockupIds = collect(old('allowed_mockup_ids', $product->personalizationMockups->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->values()->all())
@php($defaultMockupId = old('default_mockup_id', optional($product->personalizationMockups->firstWhere('pivot.is_default', true))->id ?? $product->personalizationMockups->first()?->id))

<form
    method="POST"
    action="{{ $isEdit ? route('admin.catalog.products.update', $product) : route('admin.catalog.products.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
    x-data="{
        type: @js($currentType ?: 'standard'),
        categoryId: @js((string) old('category_id', $product->category_id)),
        productName: @js(old('name', $product->name)),
        excerpt: @js(old('excerpt', $product->excerpt)),
        price: @js((string) old('price', $product->price)),
        assignedTemplateId: @js($selectedTemplateId),
        selectedMockupIds: @js($selectedMockupIds),
        defaultMockupId: @js($defaultMockupId),
        featuredImageUrl: @js(old('featured_image_url', $product->featured_image_url)),
        galleryDefaultSource: @js(old('gallery_default_source', $product->gallery_default_source ?? 'manual_featured_image')),
        showFlatPreviewFirst: @js((bool) old('show_flat_preview_first', $product->show_flat_preview_first ?? true)),
        includeMockupGallery: @js((bool) old('include_mockup_gallery', $product->include_mockup_gallery ?? true)),
        livePreviewEnabled: @js((bool) old('live_preview_enabled', $product->live_preview_enabled ?? true)),
        categories: @js($categories->map(fn ($category) => [
            'id' => (string) $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ])->values()),
        templates: @js($personalizationTemplates->map(fn ($template) => [
            'id' => $template->id,
            'name' => $template->name,
            'product_name' => $template->product?->name,
            'preview_image_url' => $template->preview_image_url,
            'base_template_url' => $template->base_template_url,
            'thumbnail_image_url' => $template->thumbnail_image_url,
            'proof_note_label' => $template->proof_note_label,
            'fields_count' => $template->fields->count(),
            'fonts_count' => $template->fonts->count(),
            'edit_url' => route('admin.personalization.templates.edit', $template),
            'mockups' => $template->mockups->map(fn ($mockup) => [
                'id' => $mockup->id,
                'title' => $mockup->title,
                'thumb_image_url' => $mockup->thumb_image_url,
                'base_image_url' => $mockup->base_image_url,
                'overlay_image_url' => $mockup->overlay_image_url,
                'edit_url' => route('admin.mockups.edit', $mockup),
                'render_mode' => $mockup->render_mode,
                'map' => $mockup->map ? [
                    'top_left_x' => (float) $mockup->map->top_left_x,
                    'top_left_y' => (float) $mockup->map->top_left_y,
                    'top_right_x' => (float) $mockup->map->top_right_x,
                    'top_right_y' => (float) $mockup->map->top_right_y,
                    'bottom_right_x' => (float) $mockup->map->bottom_right_x,
                    'bottom_right_y' => (float) $mockup->map->bottom_right_y,
                    'bottom_left_x' => (float) $mockup->map->bottom_left_x,
                    'bottom_left_y' => (float) $mockup->map->bottom_left_y,
                ] : null,
            ])->values(),
        ])->values()),
        createTemplateUrl: @js(route('admin.personalization.templates.create')),
        mockupManagerUrl: @js(route('admin.mockups.index')),
        currentTemplate() {
            return this.templates.find((template) => String(template.id) === String(this.assignedTemplateId)) || null;
        },
        selectedCategory() {
            return this.categories.find((category) => String(category.id) === String(this.categoryId)) || null;
        },
        shouldShowNikahSetup() {
            if (this.type === 'advanced_personalized') {
                return true;
            }

            const category = this.selectedCategory();

            if (! category) {
                return false;
            }

            const label = `${category.name} ${category.slug}`.toLowerCase();

            return label.includes('nikah');
        },
        availableMockups() {
            return this.currentTemplate()?.mockups || [];
        },
        syncTemplate() {
            const availableIds = this.availableMockups().map((mockup) => mockup.id);
            this.selectedMockupIds = this.selectedMockupIds.filter((id) => availableIds.includes(id));

            if (! this.selectedMockupIds.length && availableIds.length) {
                this.selectedMockupIds = [availableIds[0]];
            }

            if (! this.selectedMockupIds.includes(this.defaultMockupId)) {
                this.defaultMockupId = this.selectedMockupIds[0] || null;
            }
        },
        isMockupSelected(id) {
            return this.selectedMockupIds.includes(id);
        },
        toggleMockup(id) {
            if (this.isMockupSelected(id)) {
                this.selectedMockupIds = this.selectedMockupIds.filter((item) => item !== id);
            } else {
                this.selectedMockupIds.push(id);
            }

            if (! this.selectedMockupIds.includes(this.defaultMockupId)) {
                this.defaultMockupId = this.selectedMockupIds[0] || null;
            }
        },
        moveMockup(id, direction) {
            const currentIndex = this.selectedMockupIds.indexOf(id);

            if (currentIndex === -1) {
                return;
            }

            const targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;

            if (targetIndex < 0 || targetIndex >= this.selectedMockupIds.length) {
                return;
            }

            const reordered = [...this.selectedMockupIds];
            const [item] = reordered.splice(currentIndex, 1);
            reordered.splice(targetIndex, 0, item);
            this.selectedMockupIds = reordered;
        },
        selectedMockups() {
            const selectedIds = this.selectedMockupIds;

            return this.availableMockups().filter((mockup) => selectedIds.includes(mockup.id)).sort((a, b) => selectedIds.indexOf(a.id) - selectedIds.indexOf(b.id));
        },
        mockupPreviewFrame(mockup) {
            const map = mockup?.map;

            if (! map) {
                return {
                    left: 22,
                    top: 18,
                    width: 56,
                    height: 64,
                };
            }

            const xs = [map.top_left_x, map.top_right_x, map.bottom_right_x, map.bottom_left_x].map(Number);
            const ys = [map.top_left_y, map.top_right_y, map.bottom_right_y, map.bottom_left_y].map(Number);

            const left = Math.min(...xs) * 100;
            const top = Math.min(...ys) * 100;
            const width = (Math.max(...xs) - Math.min(...xs)) * 100;
            const height = (Math.max(...ys) - Math.min(...ys)) * 100;

            return {
                left,
                top,
                width,
                height,
            };
        },
        mockupPreviewFrameStyle(mockup) {
            const frame = this.mockupPreviewFrame(mockup);

            return `left:${frame.left}%; top:${frame.top}%; width:${frame.width}%; height:${frame.height}%;`;
        },
        storefrontPrimaryImage() {
            if (this.galleryDefaultSource === 'template_flat_preview') {
                return this.currentTemplate()?.thumbnail_image_url || this.currentTemplate()?.preview_image_url || this.currentTemplate()?.base_template_url || this.featuredImageUrl;
            }

            if (this.galleryDefaultSource === 'selected_mockup') {
                return this.selectedMockups()[0]?.thumb_image_url || this.selectedMockups()[0]?.base_image_url || this.featuredImageUrl;
            }

            return this.featuredImageUrl || this.currentTemplate()?.preview_image_url || this.currentTemplate()?.base_template_url || this.selectedMockups()[0]?.thumb_image_url || this.selectedMockups()[0]?.base_image_url || '';
        },
        swapFeaturedPreview(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            if (typeof this.featuredImageUrl === 'string' && this.featuredImageUrl.startsWith('blob:')) {
                URL.revokeObjectURL(this.featuredImageUrl);
            }

            this.featuredImageUrl = URL.createObjectURL(file);
        },
    }"
    x-init="syncTemplate()"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">1. Basic information</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Core product identity</h3>
                </div>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product name</span>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="field-input" x-model="productName">
                    @error('name') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="field-input" placeholder="Auto-generated if left blank">
                    @error('slug') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">SKU</span>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="field-input">
                    @error('sku') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Short description</span>
                    <textarea name="excerpt" rows="3" class="field-textarea" x-model="excerpt">{{ old('excerpt', $product->excerpt) }}</textarea>
                    @error('excerpt') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Full description</span>
                    <textarea name="description" rows="6" class="field-textarea">{{ old('description', $product->description) }}</textarea>
                    @error('description') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">2. Pricing and merchandising</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Commercial setup</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Price</span>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="field-input" x-model="price">
                    @error('price') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Compare at price</span>
                    <input type="number" step="0.01" min="0" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="field-input">
                    @error('compare_at_price') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Lead time in days</span>
                    <input type="number" min="0" name="lead_time_days" value="{{ old('lead_time_days', $product->lead_time_days ?? 0) }}" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Video URL</span>
                    <input type="url" name="video_url" value="{{ old('video_url', $product->video_url) }}" class="field-input" placeholder="Optional product video">
                    @error('video_url') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">3. Type, taxonomy, and publish state</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Product logic</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product type</span>
                    <select name="type" class="field-select" x-model="type">
                        @foreach ($productTypes as $type)
                            <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                    @error('type') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Status</span>
                    <select name="status" class="field-select">
                        @foreach (['draft', 'active', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Category</span>
                    <select name="category_id" class="field-select" x-model="categoryId">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Collections</span>
                    <select name="collection_ids[]" multiple class="field-select min-h-36">
                        @foreach ($collections as $collection)
                            <option value="{{ $collection->id }}" @selected(in_array((string) $collection->id, $selectedCollections, true))>{{ $collection->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Tags</span>
                    <select name="tag_ids[]" multiple class="field-select min-h-32">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTags, true))>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="surface-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">4. Media uploads</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Featured image and gallery</h3>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Upload a lead image plus supporting gallery media. Existing images can be relabeled, re-ordered, marked as primary, or removed.</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-3 text-sm text-[var(--color-text-soft)]">
                        JPG, PNG, WEBP • up to 5 MB
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Featured image</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                            <template x-if="featuredImageUrl">
                                <img :src="featuredImageUrl" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover">
                            </template>
                            <template x-if="!featuredImageUrl">
                                <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">No featured image selected yet.</div>
                            </template>
                        </div>
                        <label class="field-shell mt-4">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload featured image</span>
                            <input type="file" name="featured_image_upload" accept="image/*" class="field-input" @change="swapFeaturedPreview($event)">
                            @error('featured_image_upload') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="space-y-4">
                        <div class="surface-card-soft p-5">
                            <label class="field-shell">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload gallery images</span>
                                <input type="file" name="gallery_uploads[]" accept="image/*" multiple class="field-input">
                                <span class="text-xs text-[var(--color-text-soft)]">Bulk uploads are appended to the gallery. You can adjust labels, primary state, and sort order below after upload.</span>
                                @error('gallery_uploads') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                                @error('gallery_uploads.*') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                            </label>
                        </div>

                        <div class="grid gap-4">
                            @forelse ($existingImages as $image)
                                <article class="surface-card-soft grid gap-4 p-4 md:grid-cols-[120px_1fr]">
                                    <div class="overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                                        <img src="{{ $image->image_url }}" alt="{{ $image->alt_text ?: $product->name }}" class="aspect-square w-full object-cover">
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <label class="field-shell">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Label</span>
                                            <select name="existing_images[{{ $image->id }}][label]" class="field-select">
                                                @foreach (['front', 'detail', 'lifestyle', 'mockup', 'size-guide', 'gallery'] as $label)
                                                    <option value="{{ $label }}" @selected(old("existing_images.{$image->id}.label", $image->label) === $label)>{{ ucfirst($label) }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="field-shell">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                                            <input type="number" min="0" name="existing_images[{{ $image->id }}][position]" value="{{ old("existing_images.{$image->id}.position", $image->position) }}" class="field-input">
                                        </label>

                                        <label class="field-shell md:col-span-2">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Alt text</span>
                                            <input type="text" name="existing_images[{{ $image->id }}][alt_text]" value="{{ old("existing_images.{$image->id}.alt_text", $image->alt_text) }}" class="field-input">
                                        </label>

                                        <div class="md:col-span-2 flex flex-wrap gap-4">
                                            <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                                <input type="hidden" name="existing_images[{{ $image->id }}][is_primary]" value="0">
                                                <input type="checkbox" name="existing_images[{{ $image->id }}][is_primary]" value="1" @checked(old("existing_images.{$image->id}.is_primary", $image->is_primary)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                                Mark as primary
                                            </label>

                                            <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-danger)]">
                                                <input type="hidden" name="existing_images[{{ $image->id }}][remove]" value="0">
                                                <input type="checkbox" name="existing_images[{{ $image->id }}][remove]" value="1" class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                                Remove image
                                            </label>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="surface-card-soft p-6 text-sm leading-7 text-[var(--color-text-soft)]">No gallery images have been uploaded yet. Add a featured image plus supporting product angles to improve storefront presentation.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface-card p-6" x-show="type === 'light_customizable' || type === 'advanced_personalized'" x-cloak>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">5. Personalization settings</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Custom text and proof handling</h3>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="surface-card-soft p-5" x-show="type === 'light_customizable'">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Light customization</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Use the helper text below to explain how the customer should enter simple custom text on the storefront.</p>
                    </div>

                    <div class="surface-card-soft p-5" x-show="shouldShowNikahSetup()" x-cloak>
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Advanced personalized flow</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">This product can support proof notes, font presets, and a linked template. Template creation still lives in the dedicated personalization manager.</p>
                    </div>

                    <label class="field-shell md:col-span-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Personalization help text</span>
                        <textarea name="personalization_help_text" rows="4" class="field-textarea">{{ old('personalization_help_text', $product->personalization_help_text) }}</textarea>
                        @error('personalization_help_text') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                    </label>

                    <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]" x-show="shouldShowNikahSetup()" x-cloak>
                        <input type="hidden" name="proof_notes_enabled" value="0">
                        <input type="checkbox" name="proof_notes_enabled" value="1" @checked(old('proof_notes_enabled', $product->proof_notes_enabled)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Enable customer proof notes
                    </label>

                    <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]" x-show="shouldShowNikahSetup()" x-cloak>
                        <input type="hidden" name="font_presets_enabled" value="0">
                        <input type="checkbox" name="font_presets_enabled" value="1" @checked(old('font_presets_enabled', $product->font_presets_enabled)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Enable font presets
                    </label>

                    <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]" x-show="shouldShowNikahSetup()" x-cloak>
                        <input type="hidden" name="live_preview_enabled" value="0">
                        <input type="checkbox" name="live_preview_enabled" value="1" x-model="livePreviewEnabled" @checked(old('live_preview_enabled', $product->live_preview_enabled ?? true)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Enable storefront live preview
                    </label>

                    <div class="surface-card-soft p-5 md:col-span-2" x-show="shouldShowNikahSetup()" x-cloak>
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Nikah personalization and mockup setup</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Connect the flat certificate template, choose storefront mockups, and preview how this Nikah product will look before saving.</p>
                            </div>
                            <a href="#" class="button-ghost !px-3 !py-2" @click.prevent="window.open(`/products/${'{{ old('slug', $product->slug) }}' || ''}`, '_blank')" x-show="@js($isEdit)">Open storefront preview</a>
                        </div>

                        <div class="mt-5 grid gap-5 xl:grid-cols-[1.06fr_0.94fr]">
                            <div class="space-y-5">
                                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/85 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Assigned personalization template</p>
                                            <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Use one active Nikah template as the flat certificate source for this product.</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a :href="currentTemplate()?.edit_url || createTemplateUrl" target="_blank" class="button-ghost !px-3 !py-2 text-xs" x-text="currentTemplate() ? 'Edit personalization template' : 'Create new template'"></a>
                                            <a :href="createTemplateUrl" target="_blank" class="button-ghost !px-3 !py-2 text-xs">Create new template</a>
                                        </div>
                                    </div>

                                    <label class="field-shell mt-4">
                                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Assigned Personalization Template</span>
                                        <select name="assigned_template_id" class="field-select" x-model="assignedTemplateId" @change="syncTemplate()">
                                            <option value="">Select a template</option>
                                            @foreach ($personalizationTemplates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}{{ $template->product ? ' • '.$template->product->name : '' }}</option>
                                            @endforeach
                                        </select>
                                        @error('assigned_template_id') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                                    </label>

                                    <template x-if="currentTemplate()">
                                        <div class="mt-4 rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-[var(--bg-section-soft)] p-4">
                                            <div class="grid gap-4 md:grid-cols-[104px_1fr]">
                                                <div class="overflow-hidden rounded-[20px] border border-[var(--color-border-soft)] bg-white">
                                                    <img :src="currentTemplate().thumbnail_image_url || currentTemplate().preview_image_url || currentTemplate().base_template_url" alt="" class="aspect-[4/5] w-full object-cover">
                                                </div>
                                                <div class="space-y-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="currentTemplate().name"></p>
                                                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Assigned product: <span x-text="currentTemplate().product_name || 'Unassigned draft'"></span></p>
                                                    </div>
                                                    <div class="grid gap-3 sm:grid-cols-3">
                                                        <div class="rounded-[18px] border border-[var(--color-border-soft)] bg-white px-3 py-3">
                                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--color-primary-900)]">Fields</p>
                                                            <p class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="currentTemplate().fields_count"></p>
                                                        </div>
                                                        <div class="rounded-[18px] border border-[var(--color-border-soft)] bg-white px-3 py-3">
                                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--color-primary-900)]">Fonts</p>
                                                            <p class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="currentTemplate().fonts_count"></p>
                                                        </div>
                                                        <div class="rounded-[18px] border border-[var(--color-border-soft)] bg-white px-3 py-3">
                                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--color-primary-900)]">Proof label</p>
                                                            <p class="mt-1 text-xs font-semibold text-[var(--color-secondary-900)]" x-text="currentTemplate().proof_note_label || 'Default label'"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/85 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Mockup selection</p>
                                            <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Choose multiple mockups, reorder them, and mark the default storefront scene.</p>
                                        </div>
                                        <a :href="mockupManagerUrl" target="_blank" class="button-ghost !px-3 !py-2 text-xs">Open mockup manager</a>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <label class="field-shell">
                                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default gallery image source</span>
                                            <select name="gallery_default_source" class="field-select" x-model="galleryDefaultSource">
                                                <option value="manual_featured_image">Manual featured image</option>
                                                <option value="template_flat_preview">Template flat preview</option>
                                                <option value="selected_mockup">Selected mockup</option>
                                            </select>
                                        </label>
                                        <div class="rounded-[20px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-3 text-xs leading-6 text-[var(--color-text-soft)]">
                                            Mockups are filtered to the selected template’s scene library.
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-4">
                                        <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                            <input type="hidden" name="show_flat_preview_first" value="0">
                                            <input type="checkbox" name="show_flat_preview_first" value="1" x-model="showFlatPreviewFirst" @checked(old('show_flat_preview_first', $product->show_flat_preview_first ?? true)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                            Show flat preview first
                                        </label>
                                        <label class="inline-flex items-center gap-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                            <input type="hidden" name="include_mockup_gallery" value="0">
                                            <input type="checkbox" name="include_mockup_gallery" value="1" x-model="includeMockupGallery" @checked(old('include_mockup_gallery', $product->include_mockup_gallery ?? true)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                            Include selected mockups in storefront gallery
                                        </label>
                                    </div>

                                    <div class="mt-4 grid gap-4">
                                        <template x-if="!currentTemplate()">
                                            <div class="rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-5 text-sm text-[var(--color-text-soft)]">Select an active personalization template first to unlock mockup selection.</div>
                                        </template>

                                        <template x-if="currentTemplate() && !availableMockups().length">
                                            <div class="rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-5 text-sm text-[var(--color-text-soft)]">No active mockups are available for the selected template yet.</div>
                                        </template>

                                        <template x-for="mockup in availableMockups()" :key="mockup.id">
                                            <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/90 p-4 md:grid-cols-[116px_1fr]">
                                                <div class="overflow-hidden rounded-[20px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                                    <img :src="mockup.thumb_image_url || mockup.base_image_url" alt="" class="aspect-square w-full object-cover">
                                                </div>
                                                <div class="space-y-3">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]" x-text="mockup.title"></p>
                                                            <p class="text-xs text-[var(--color-text-soft)]" x-text="mockup.render_mode"></p>
                                                        </div>
                                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-[var(--color-secondary-900)]">
                                                            <input type="checkbox" :checked="isMockupSelected(mockup.id)" @change="toggleMockup(mockup.id)" class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                                            Enable
                                                        </label>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <a :href="mockup.edit_url" target="_blank" class="button-ghost !px-3 !py-2 text-xs">Edit mockup</a>
                                                        <template x-if="isMockupSelected(mockup.id)">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <input type="hidden" name="allowed_mockup_ids[]" :value="mockup.id">
                                                                <button type="button" class="button-ghost !px-3 !py-2 text-xs" @click="moveMockup(mockup.id, 'up')">Move up</button>
                                                                <button type="button" class="button-ghost !px-3 !py-2 text-xs" @click="moveMockup(mockup.id, 'down')">Move down</button>
                                                                <label class="inline-flex items-center gap-2 rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-2 text-[11px] font-semibold text-[var(--color-secondary-900)]">
                                                                    <input type="radio" name="default_mockup_id" :value="mockup.id" x-model="defaultMockupId" class="h-4 w-4 border-[var(--color-border-soft)]">
                                                                    Default mockup
                                                                </label>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    @error('allowed_mockup_ids') <span class="mt-3 block text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                                    @error('allowed_mockup_ids.*') <span class="mt-3 block text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-[var(--bg-section-soft)] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Storefront preview</p>
                                            <p class="mt-1 text-xs leading-6 text-[var(--color-text-soft)]">Flat Nikah preview first, then selected mockup scenes with the certificate placed inside each mapped area.</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-[var(--color-secondary-900)]" x-text="selectedMockups().length ? `${selectedMockups().length} mockups selected` : 'No mockups selected'"></span>
                                    </div>

                                    <div class="mt-4 grid gap-4">
                                        <div class="rounded-[22px] border border-[var(--color-border-soft)] bg-white p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Flat preview</p>
                                                    <p class="mt-1 text-sm font-semibold text-[var(--color-secondary-900)]" x-text="currentTemplate()?.name || 'Awaiting template selection'"></p>
                                                </div>
                                                <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-3 py-1 text-[11px] font-semibold text-[var(--color-primary-900)]" x-show="showFlatPreviewFirst">First in gallery</span>
                                            </div>
                                            <div class="mt-3 overflow-hidden rounded-[20px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                                <template x-if="currentTemplate()">
                                                    <img :src="currentTemplate().thumbnail_image_url || currentTemplate().preview_image_url || currentTemplate().base_template_url" alt="" class="aspect-[4/5] w-full object-cover">
                                                </template>
                                                <template x-if="!currentTemplate()">
                                                    <div class="flex aspect-[4/5] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Select a Nikah template to preview the flat certificate.</div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                                            <template x-for="mockup in selectedMockups().slice(0, 3)" :key="mockup.id">
                                                <div class="rounded-[22px] border border-[var(--color-border-soft)] bg-white p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mockup preview</p>
                                                            <p class="mt-1 text-sm font-semibold text-[var(--color-secondary-900)]" x-text="mockup.title"></p>
                                                        </div>
                                                        <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-[11px] font-semibold text-[var(--color-secondary-900)]" x-show="defaultMockupId === mockup.id">Default</span>
                                                    </div>
                                                    <div class="mt-3 relative overflow-hidden rounded-[20px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                                        <img :src="mockup.base_image_url" alt="" class="aspect-[4/3] w-full object-cover">
                                                        <template x-if="currentTemplate()">
                                                            <div class="absolute overflow-hidden rounded-[10px] border border-white/70 shadow-[0_14px_30px_rgba(0,48,73,0.2)]" :style="mockupPreviewFrameStyle(mockup)">
                                                                <img :src="currentTemplate().thumbnail_image_url || currentTemplate().preview_image_url || currentTemplate().base_template_url" alt="" class="h-full w-full object-cover">
                                                            </div>
                                                        </template>
                                                        <template x-if="mockup.overlay_image_url">
                                                            <img :src="mockup.overlay_image_url" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-80">
                                                        </template>
                                                    </div>
                                                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-[var(--color-text-soft)]">
                                                        <span x-text="mockup.render_mode"></span>
                                                        <a :href="mockup.edit_url" target="_blank" class="font-semibold text-[var(--color-secondary-900)]">Edit mockup</a>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface-card p-6" x-show="type !== 'service'" x-cloak>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">6. Inventory and variants</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Stock-aware setup</h3>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-3">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Stock quantity</span>
                        <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" class="field-input">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Low stock threshold</span>
                        <input type="number" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 0) }}" class="field-input">
                    </label>

                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="manage_stock" value="0">
                        <input type="checkbox" name="manage_stock" value="1" @checked(old('manage_stock', $product->manage_stock)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Track stock for this product
                    </label>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($variantRows as $index => $variant)
                        <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/70 p-4 md:grid-cols-6">
                            <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" placeholder="Variant name" class="field-input">
                            <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="SKU" class="field-input">
                            <input type="text" name="variants[{{ $index }}][option_values]" value="{{ $variant['option_values'] ?? '' }}" placeholder="red, medium" class="field-input">
                            <input type="number" step="0.01" min="0" name="variants[{{ $index }}][price]" value="{{ $variant['price'] ?? '' }}" placeholder="Price" class="field-input">
                            <input type="number" min="0" name="variants[{{ $index }}][stock_quantity]" value="{{ $variant['stock_quantity'] ?? '' }}" placeholder="Stock" class="field-input">
                            <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                                <input type="hidden" name="variants[{{ $index }}][is_default]" value="0">
                                <input type="checkbox" name="variants[{{ $index }}][is_default]" value="1" @checked($variant['is_default'] ?? false) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                                Default
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-6" x-show="type === 'bundle'" x-cloak>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">7. Bundle builder</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Combo composition</h3>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($bundleRows as $index => $bundleItem)
                        <div class="grid gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/70 p-4 md:grid-cols-[1fr_180px]">
                            <select name="bundle_items[{{ $index }}][child_product_id]" class="field-select">
                                <option value="">Select child product</option>
                                @foreach ($relatedProducts as $relatedProduct)
                                    <option value="{{ $relatedProduct->id }}" @selected((string) ($bundleItem['child_product_id'] ?? '') === (string) $relatedProduct->id)>{{ $relatedProduct->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" min="1" name="bundle_items[{{ $index }}][quantity]" value="{{ $bundleItem['quantity'] ?? 1 }}" class="field-input" placeholder="Quantity">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-6" x-show="type === 'service'" x-cloak>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">8. Service booking details</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Booking-specific metadata</h3>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Service type</span>
                        <input type="text" name="service_meta[service_type]" value="{{ $serviceMeta['service_type'] ?? '' }}" class="field-input">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Duration label</span>
                        <input type="text" name="service_meta[duration_label]" value="{{ $serviceMeta['duration_label'] ?? '' }}" class="field-input">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Location scope</span>
                        <input type="text" name="service_meta[location_scope]" value="{{ $serviceMeta['location_scope'] ?? '' }}" class="field-input">
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Advance payment amount</span>
                        <input type="number" step="0.01" min="0" name="service_meta[advance_payment_amount]" value="{{ $serviceMeta['advance_payment_amount'] ?? '' }}" class="field-input">
                    </label>

                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="service_meta[requires_advance_payment]" value="0">
                        <input type="checkbox" name="service_meta[requires_advance_payment]" value="1" @checked((bool) ($serviceMeta['requires_advance_payment'] ?? false)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Requires advance payment
                    </label>

                    <label class="field-shell md:col-span-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Booking notes</span>
                        <textarea name="service_meta[booking_notes]" rows="4" class="field-textarea">{{ $serviceMeta['booking_notes'] ?? '' }}</textarea>
                    </label>
                </div>
            </div>

            <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">9. SEO and related products</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Discovery helpers</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}" class="field-input">
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Related products</span>
                    <select name="related_product_ids[]" multiple class="field-select min-h-36">
                        @foreach ($relatedProducts as $relatedProduct)
                            <option value="{{ $relatedProduct->id }}" @selected(in_array((string) $relatedProduct->id, $selectedRelated, true))>{{ $relatedProduct->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Related categories</span>
                    <select name="related_category_ids[]" multiple class="field-select min-h-32">
                        @foreach ($relatedCategories as $relatedCategory)
                            <option value="{{ $relatedCategory->id }}" @selected(in_array((string) $relatedCategory->id, $selectedRelatedCategories, true))>{{ $relatedCategory->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-card p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">10. Publish settings</p>
                <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Storefront preview sidebar</h3>

                <div class="mt-6 space-y-4">
                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Feature this product on the storefront
                    </label>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Current storefront snapshot</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                            <template x-if="storefrontPrimaryImage()">
                                <img :src="storefrontPrimaryImage()" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover">
                            </template>
                            <template x-if="!storefrontPrimaryImage()">
                                <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Featured storefront preview will appear once the lead image is saved.</div>
                            </template>
                        </div>
                        <p class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="productName || 'Product title preview'"></p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]" x-text="excerpt || 'Use the short description to shape listing cards and admin previews.'"></p>
                        <div class="mt-4 flex items-center justify-between rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-3">
                            <span class="text-sm text-[var(--color-text-soft)]">Price block</span>
                            <span class="text-base font-semibold text-[var(--color-secondary-900)]" x-text="price ? `BDT ${price}` : 'BDT 0.00'"></span>
                        </div>
                    </div>

                    <div class="surface-card-soft p-5" x-show="shouldShowNikahSetup()" x-cloak>
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Nikah gallery summary</p>
                        <div class="mt-4 space-y-3 text-sm text-[var(--color-text-soft)]">
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Template:</span> <span x-text="currentTemplate()?.name || 'Not assigned yet'"></span></p>
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Selected mockups:</span> <span x-text="selectedMockups().length"></span></p>
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Primary source:</span> <span x-text="galleryDefaultSource.replaceAll('_', ' ')"></span></p>
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Flat preview first:</span> <span x-text="showFlatPreviewFirst ? 'Yes' : 'No'"></span></p>
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Mockups in gallery:</span> <span x-text="includeMockupGallery ? 'Enabled' : 'Hidden'"></span></p>
                            <p><span class="font-semibold text-[var(--color-secondary-900)]">Live preview:</span> <span x-text="livePreviewEnabled ? 'Enabled' : 'Disabled'"></span></p>
                        </div>

                        <div class="mt-4 grid gap-3">
                            <template x-for="mockup in selectedMockups().slice(0, 3)" :key="mockup.id">
                                <div class="flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white px-3 py-3">
                                    <img :src="mockup.thumb_image_url || mockup.base_image_url" alt="" class="h-14 w-14 rounded-xl object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-[var(--color-secondary-900)]" x-text="mockup.title"></p>
                                        <p class="truncate text-xs text-[var(--color-text-soft)]" x-text="mockup.render_mode"></p>
                                    </div>
                                    <span class="rounded-full bg-[var(--bg-section-soft)] px-2 py-1 text-[11px] font-semibold text-[var(--color-secondary-900)]" x-show="defaultMockupId === mockup.id">Default</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Checklist</p>
                        <div class="mt-4 space-y-3 text-sm text-[var(--color-text-soft)]">
                            <p x-text="featuredImageUrl ? 'Lead image is ready.' : 'Lead image still needs to be uploaded.'"></p>
                            <p>{{ $product->images->count() > 0 ? 'Gallery coverage exists.' : 'Gallery images are still missing.' }}</p>
                            <p x-show="type === 'advanced_personalized'" x-text="currentTemplate() ? 'Advanced product has a linked template.' : 'Advanced product still needs a linked template.'"></p>
                            <p x-show="type !== 'advanced_personalized'">Type-specific requirements look okay for this phase.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save product changes' : 'Create product' }}</button>
        <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
