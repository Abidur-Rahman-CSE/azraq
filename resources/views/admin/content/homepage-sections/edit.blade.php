<x-layouts.admin title="Edit Homepage Section | Azraq Bridal">
    @php
        $selectedCollectionIds = collect(old('settings.selected_collection_ids', data_get($settings ?? [], 'selected_collection_ids', [])))
            ->map(fn ($id) => (string) $id)
            ->all();
        $selectedProductIds = collect(old('settings.selected_product_ids', data_get($settings ?? [], 'selected_product_ids', [])))
            ->map(fn ($id) => (string) $id)
            ->all();
        $selectedCategoryIds = collect(old('settings.selected_category_ids', data_get($settings ?? [], 'selected_category_ids', [])))
            ->map(fn ($id) => (string) $id)
            ->all();
        $desktopImageUrl = old('settings.desktop_image_url', data_get($settings ?? [], 'desktop_image_url'));
        $mobileImageUrl = old('settings.mobile_image_url', data_get($settings ?? [], 'mobile_image_url'));
        $secondaryCtaLabel = old('settings.secondary_cta_label', data_get($settings ?? [], 'secondary_cta_label'));
        $secondaryCtaHref = old('settings.secondary_cta_href', data_get($settings ?? [], 'secondary_cta_href'));
        $featuredProductId = old('settings.featured_product_id', data_get($settings ?? [], 'featured_product_id'));
        $statRows = old('settings.stats', data_get($settings ?? [], 'stats', []));
        if (! is_array($statRows) || count($statRows) === 0) {
            $statRows = [['num' => '', 'label' => '']];
        }
        $spotlightProductId = old('settings.product_id', data_get($settings ?? [], 'product_id'));
        $processSteps = old('settings.process_steps', data_get($settings ?? [], 'process_steps', []));
        if (! is_array($processSteps) || count($processSteps) === 0) {
            $processSteps = ['', '', ''];
        }
        $serviceIds = collect(old('settings.service_ids', data_get($settings ?? [], 'service_ids', [])))
            ->map(fn ($id) => (string) $id)
            ->all();
        $finaleBgUrl = old('settings.background_image_url', data_get($settings ?? [], 'background_image_url'));
        $instaPosts = old('settings.posts', data_get($settings ?? [], 'posts', []));
        if (! is_array($instaPosts) || count($instaPosts) === 0) {
            $instaPosts = [['image_url' => '', 'href' => '']];
        }
        $trustSignals = old('settings.signals', data_get($settings ?? [], 'signals', []));
        if (! is_array($trustSignals) || count($trustSignals) === 0) {
            $trustSignals = [['icon' => '◆', 'label' => '']];
        }
    @endphp
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Edit homepage section</h2>
        </div>
        <form method="POST" action="{{ route('admin.content.homepage-sections.update', $section) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <section class="surface-card grid gap-6 p-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Title</span>
                    <input type="text" name="title" value="{{ old('title', $section->title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Subtitle</span>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Content</span>
                    <textarea name="content" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">{{ old('content', $section->content) }}</textarea>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">CTA label</span>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $section->cta_label) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">CTA href</span>
                    <input type="text" name="cta_href" value="{{ old('cta_href', $section->cta_href) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                </label>
                <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                    <input type="hidden" name="is_enabled" value="0">
                    <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $section->is_enabled)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Enabled
                </label>
            </section>

            @if ($section->section_key === 'hero')
                <section class="surface-card grid gap-6 p-6 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Hero images</p>
                            <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Upload campaign visuals</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">These images override the default featured-product collage in the homepage hero.</p>
                        </div>

                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Desktop hero image</span>
                            <input type="file" name="desktop_image_upload" accept="image/*" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <input type="text" name="settings[desktop_image_url]" value="{{ $desktopImageUrl }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Or paste an image URL">
                        </label>

                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Mobile hero image</span>
                            <input type="file" name="mobile_image_upload" accept="image/*" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <input type="text" name="settings[mobile_image_url]" value="{{ $mobileImageUrl }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Optional mobile-specific image URL">
                        </label>

                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA label</span>
                                <input type="text" name="settings[secondary_cta_label]" value="{{ $secondaryCtaLabel }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="See curated editions ↓">
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA href</span>
                                <input type="text" name="settings[secondary_cta_href]" value="{{ $secondaryCtaHref }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="#curated">
                            </label>
                        </div>

                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Featured product chip (overlay)</span>
                            <select name="settings[featured_product_id]" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                                <option value="">— auto (signature Nikah) —</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected((string) $featuredProductId === (string) $product->id)>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Desktop preview</p>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                @if ($desktopImageUrl)
                                    <img src="{{ $desktopImageUrl }}" alt="Desktop hero preview" class="aspect-[4/5] w-full object-cover">
                                @else
                                    <div class="flex aspect-[4/5] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Falls back to the first featured product image.</div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Mobile preview</p>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                @if ($mobileImageUrl)
                                    <img src="{{ $mobileImageUrl }}" alt="Mobile hero preview" class="aspect-[4/5] w-full object-cover">
                                @else
                                    <div class="flex aspect-[4/5] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Desktop image is used when no mobile image is supplied.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'featured_collections')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Featured collection items</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Choose which collections appear on the homepage</h3>
                    </div>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Selected collections</span>
                        <select name="settings[selected_collection_ids][]" multiple class="min-h-56 w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach ($collections as $collection)
                                <option value="{{ $collection->id }}" @selected(in_array((string) $collection->id, $selectedCollectionIds, true))>{{ $collection->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($collections->whereIn('id', array_map('intval', $selectedCollectionIds)) as $collection)
                            <article class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                                <div class="overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                    @if ($collection->cover_image_url)
                                        <img src="{{ $collection->cover_image_url }}" alt="{{ $collection->name }}" class="aspect-[4/3] w-full object-cover">
                                    @else
                                        <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Upload a collection cover image for homepage merchandising.</div>
                                    @endif
                                </div>
                                <h4 class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $collection->name }}</h4>
                                <div class="mt-4 flex gap-3">
                                    <a href="{{ route('admin.catalog.collections.edit', $collection) }}" class="button-ghost">Edit collection</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'featured_products')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Featured products</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Select homepage product cards</h3>
                    </div>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Selected products</span>
                        <select name="settings[selected_product_ids][]" multiple class="min-h-56 w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array((string) $product->id, $selectedProductIds, true))>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products->whereIn('id', array_map('intval', $selectedProductIds)) as $product)
                            <article class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                                <div class="overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                    @if ($product->storefront_preview_image_url)
                                        <img src="{{ $product->storefront_preview_image_url }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover">
                                    @else
                                        <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Add product media or a personalized default mockup preview.</div>
                                    @endif
                                </div>
                                <h4 class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $product->name }}</h4>
                                <div class="mt-4 flex gap-3">
                                    <a href="{{ route('admin.catalog.products.edit', $product) }}" class="button-ghost">Edit product</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'featured_categories')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Featured categories</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Choose homepage category tiles</h3>
                    </div>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Selected categories</span>
                        <select name="settings[selected_category_ids][]" multiple class="min-h-56 w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(in_array((string) $category->id, $selectedCategoryIds, true))>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($categories->whereIn('id', array_map('intval', $selectedCategoryIds)) as $category)
                            <article class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                                <div class="overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                    @if ($category->banner_image_url ?: $category->image_url)
                                        <img src="{{ $category->banner_image_url ?: $category->image_url }}" alt="{{ $category->alt_text ?: $category->name }}" class="aspect-[4/3] w-full object-cover">
                                    @else
                                        <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Upload category image or banner for homepage cards.</div>
                                    @endif
                                </div>
                                <h4 class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $category->name }}</h4>
                                <div class="mt-4 flex gap-3">
                                    <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="button-ghost">Edit category</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'stats_strip')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Stats strip</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Edit the trust numbers</h3>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Up to 6 rows. Two-up on mobile, four-up on desktop.</p>
                    </div>
                    <div class="space-y-3">
                        @foreach ($statRows as $idx => $row)
                            <div class="grid gap-3 md:grid-cols-[180px_1fr]">
                                <input type="text" name="settings[stats][{{ $idx }}][num]" value="{{ $row['num'] ?? '' }}" placeholder="350+" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                                <input type="text" name="settings[stats][{{ $idx }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Brides served" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            </div>
                        @endforeach
                        <div class="grid gap-3 md:grid-cols-[180px_1fr]">
                            <input type="text" name="settings[stats][{{ count($statRows) }}][num]" placeholder="Add new (number)" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <input type="text" name="settings[stats][{{ count($statRows) }}][label]" placeholder="Add new (label)" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        </div>
                    </div>
                    <p class="text-xs text-[var(--color-text-soft)]">Empty rows are removed on save. To delete a row, clear both fields.</p>
                </section>
            @endif

            @if ($section->section_key === 'signature_nikah_spotlight')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Signature Nikah spotlight</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Featured product + process steps</h3>
                    </div>
                    <label class="space-y-2 block">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Spotlight product</span>
                        <select name="settings[product_id]" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <option value="">— auto (first Nikah personalization product) —</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) $spotlightProductId === (string) $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-[var(--color-secondary-900)]">Process steps (3 inline)</p>
                        @foreach ($processSteps as $idx => $step)
                            <input type="text" name="settings[process_steps][{{ $idx }}]" value="{{ $step }}" placeholder="01 Fill details" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        @endforeach
                        <input type="text" name="settings[process_steps][{{ count($processSteps) }}]" placeholder="Add another step" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA label</span>
                            <input type="text" name="settings[secondary_cta_label]" value="{{ $secondaryCtaLabel }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Explore Nikah Collection">
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA href</span>
                            <input type="text" name="settings[secondary_cta_href]" value="{{ $secondaryCtaHref }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="/categories/nikah-collection">
                        </label>
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'atelier_services')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Atelier services</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Choose service products to highlight</h3>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Leave empty to auto-select all service-type products.</p>
                    </div>
                    <label class="space-y-2 block">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Selected services</span>
                        <select name="settings[service_ids][]" multiple class="min-h-56 w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array((string) $product->id, $serviceIds, true))>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </section>
            @endif

            @if ($section->section_key === 'finale_cta')
                <section class="surface-card grid gap-6 p-6 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Finale CTA</p>
                            <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Cinematic close</h3>
                        </div>
                        <label class="space-y-2 block">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Background image</span>
                            <input type="file" name="background_image_upload" accept="image/*" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <input type="text" name="settings[background_image_url]" value="{{ $finaleBgUrl }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Or paste an image URL">
                        </label>
                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA label</span>
                                <input type="text" name="settings[secondary_cta_label]" value="{{ $secondaryCtaLabel }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="Browse the shop">
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Secondary CTA href</span>
                                <input type="text" name="settings[secondary_cta_href]" value="{{ $secondaryCtaHref }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3" placeholder="/shop">
                            </label>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-[var(--color-border-soft)] bg-white p-4">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Preview</p>
                        <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                            @if ($finaleBgUrl)
                                <img src="{{ $finaleBgUrl }}" alt="Finale CTA preview" class="aspect-[16/9] w-full object-cover">
                            @else
                                <div class="flex aspect-[16/9] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Falls back to a brand gradient if no image is uploaded.</div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'instagram_strip')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Instagram strip</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Add Instagram-style image links</h3>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Up to 12 posts. Each row: square image URL + Instagram post link.</p>
                    </div>
                    <div class="space-y-3">
                        @foreach ($instaPosts as $idx => $post)
                            <div class="grid gap-3 md:grid-cols-2">
                                <input type="text" name="settings[posts][{{ $idx }}][image_url]" value="{{ $post['image_url'] ?? '' }}" placeholder="https://… (square image URL)" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                                <input type="text" name="settings[posts][{{ $idx }}][href]" value="{{ $post['href'] ?? '' }}" placeholder="https://instagram.com/p/…" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            </div>
                        @endforeach
                        <div class="grid gap-3 md:grid-cols-2">
                            <input type="text" name="settings[posts][{{ count($instaPosts) }}][image_url]" placeholder="Add new (image URL)" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <input type="text" name="settings[posts][{{ count($instaPosts) }}][href]" placeholder="Add new (link)" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        </div>
                    </div>
                </section>
            @endif

            @if ($section->section_key === 'trust_strip')
                <section class="surface-card space-y-6 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Trust strip</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Trust signals shown above the footer</h3>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Up to 6 rows. Icon is a single glyph (◆ ✦ ◈ ❋ ✧).</p>
                    </div>
                    <div class="space-y-3">
                        @foreach ($trustSignals as $idx => $signal)
                            <div class="grid gap-3 md:grid-cols-[80px_1fr]">
                                <input type="text" name="settings[signals][{{ $idx }}][icon]" value="{{ $signal['icon'] ?? '◆' }}" maxlength="4" placeholder="◆" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 text-center">
                                <input type="text" name="settings[signals][{{ $idx }}][label]" value="{{ $signal['label'] ?? '' }}" placeholder="Hand-finished in Dhaka" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            </div>
                        @endforeach
                        <div class="grid gap-3 md:grid-cols-[80px_1fr]">
                            <input type="text" name="settings[signals][{{ count($trustSignals) }}][icon]" placeholder="◆" maxlength="4" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 text-center">
                            <input type="text" name="settings[signals][{{ count($trustSignals) }}][label]" placeholder="Add new" class="rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        </div>
                    </div>
                </section>
            @endif

            <div>
                <button type="submit" class="button-primary">Save section</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
