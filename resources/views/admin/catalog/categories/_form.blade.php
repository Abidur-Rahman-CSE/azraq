@php($isEdit = $category->exists)
@php($selectedRelated = collect(old('related_category_ids', $category->relatedCategories->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())

<form
    method="POST"
    action="{{ $isEdit ? route('admin.catalog.categories.update', $category) : route('admin.catalog.categories.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
    x-data="{
        categoryName: @js(old('name', $category->name)),
        shortDescription: @js(old('description', $category->description)),
        storefrontExcerpt: @js(old('storefront_excerpt', $category->storefront_excerpt)),
        cardImageUrl: @js(old('image_url', $category->image_url)),
        bannerImageUrl: @js(old('banner_image_url', $category->banner_image_url)),
        mobileBannerUrl: @js(old('mobile_banner_image_url', $category->mobile_banner_image_url)),
        iconImageUrl: @js(old('icon_image_url', $category->icon_image_url)),
        seoImageUrl: @js(old('seo_image_url', $category->seo_image_url)),
        swapPreview(key, event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            if (typeof this[key] === 'string' && this[key].startsWith('blob:')) {
                URL.revokeObjectURL(this[key]);
            }

            this[key] = URL.createObjectURL(file);
        },
    }"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="admin-category-editor">
        <div class="admin-category-editor__form space-y-6">
            <div class="surface-card admin-category-card-grid p-6">
                <div class="admin-category-card-grid__full">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">1. Category information</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Core structure</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Category name</span>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="field-input" x-model="categoryName">
                    @error('name') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="field-input" placeholder="Auto-generated if left blank">
                    @error('slug') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Parent category</span>
                    <select name="parent_id" class="field-select">
                        <option value="">None</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="field-input">
                </label>

                <label class="field-shell admin-category-card-grid__full">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Short description</span>
                    <textarea name="description" rows="5" class="field-textarea" x-model="shortDescription">{{ old('description', $category->description) }}</textarea>
                    @error('description') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell admin-category-card-grid__full">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Storefront excerpt</span>
                    <textarea name="storefront_excerpt" rows="3" class="field-textarea" x-model="storefrontExcerpt" placeholder="Short browse-page summary used on cards, collection strips, and category promos.">{{ old('storefront_excerpt', $category->storefront_excerpt) }}</textarea>
                    @error('storefront_excerpt') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">2. Image assets</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Category media previews</h3>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Upload a category image, a full banner, optional mobile banner, optional icon, and a dedicated SEO image. These assets feed both browse pages and homepage sections.</p>
                </div>

                <div class="admin-category-media-grid mt-6">
                    @foreach ([
                        ['label' => 'Category image', 'name' => 'image_upload', 'current' => old('image_url', $category->image_url), 'hint' => 'Use 4:3 ratio, recommended 1200 x 900 px for cards and category tiles.'],
                        ['label' => 'Banner image', 'name' => 'banner_upload', 'current' => old('banner_image_url', $category->banner_image_url), 'hint' => 'Use 16:7 ratio, recommended 1920 x 840 px for category hero banners.'],
                        ['label' => 'Mobile banner', 'name' => 'mobile_banner_upload', 'current' => old('mobile_banner_image_url', $category->mobile_banner_image_url), 'hint' => 'Use 4:5 ratio, recommended 1080 x 1350 px for phone hero banners.'],
                        ['label' => 'Icon image', 'name' => 'icon_upload', 'current' => old('icon_image_url', $category->icon_image_url), 'hint' => 'Use 1:1 square ratio, recommended 512 x 512 px for compact category navigation.'],
                        ['label' => 'SEO image', 'name' => 'seo_image_upload', 'current' => old('seo_image_url', $category->seo_image_url), 'hint' => 'Use 1.91:1 ratio, recommended 1200 x 630 px for social cards and SEO previews.'],
                    ] as $asset)
                        <div class="surface-card-soft p-5 {{ $loop->last ? 'admin-category-media-grid__wide' : '' }}">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $asset['label'] }}</p>
                            <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                                <template x-if="{{ match($asset['name']) {
                                    'image_upload' => 'cardImageUrl',
                                    'banner_upload' => 'bannerImageUrl',
                                    'mobile_banner_upload' => 'mobileBannerUrl',
                                    'icon_upload' => 'iconImageUrl',
                                    default => 'seoImageUrl',
                                } }}">
                                    <img :src="{{ match($asset['name']) {
                                        'image_upload' => 'cardImageUrl',
                                        'banner_upload' => 'bannerImageUrl',
                                        'mobile_banner_upload' => 'mobileBannerUrl',
                                        'icon_upload' => 'iconImageUrl',
                                        default => 'seoImageUrl',
                                    } }}" alt="{{ $category->alt_text ?: $category->name }}" class="aspect-[4/3] w-full object-cover">
                                </template>
                                <template x-if="!{{ match($asset['name']) {
                                    'image_upload' => 'cardImageUrl',
                                    'banner_upload' => 'bannerImageUrl',
                                    'mobile_banner_upload' => 'mobileBannerUrl',
                                    'icon_upload' => 'iconImageUrl',
                                    default => 'seoImageUrl',
                                } }}">
                                    <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">No asset uploaded yet.</div>
                                </template>
                            </div>
                            <label class="field-shell mt-4">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload {{ strtolower($asset['label']) }}</span>
                                <input type="file" name="{{ $asset['name'] }}" accept="image/*" class="field-input" @change="swapPreview('{{ match($asset['name']) {
                                    'image_upload' => 'cardImageUrl',
                                    'banner_upload' => 'bannerImageUrl',
                                    'mobile_banner_upload' => 'mobileBannerUrl',
                                    'icon_upload' => 'iconImageUrl',
                                    default => 'seoImageUrl',
                                } }}', $event)">
                                <span class="text-xs text-[var(--color-text-soft)]">{{ $asset['hint'] }}</span>
                                @error($asset['name']) <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                            </label>
                        </div>
                    @endforeach
                </div>

                <label class="field-shell mt-6">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Alt text</span>
                    <input type="text" name="alt_text" value="{{ old('alt_text', $category->alt_text) }}" class="field-input">
                    @error('alt_text') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="surface-card admin-category-card-grid p-6">
                <div class="admin-category-card-grid__full">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">3. Homepage and related categories</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Merchandising controls</h3>
                </div>

                <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Active category
                </label>

                <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $category->is_featured)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Featured category
                </label>

                <label class="admin-category-card-grid__full inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <input type="hidden" name="show_on_homepage" value="0">
                    <input type="checkbox" name="show_on_homepage" value="1" @checked(old('show_on_homepage', $category->show_on_homepage)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Show this category on homepage and curated storefront sections
                </label>

                <label class="field-shell admin-category-card-grid__full">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Related categories</span>
                    <select name="related_category_ids[]" multiple class="field-select min-h-40">
                        @foreach ($relatedCategories as $relatedCategory)
                            <option value="{{ $relatedCategory->id }}" @selected(in_array((string) $relatedCategory->id, $selectedRelated, true))>{{ $relatedCategory->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="surface-card admin-category-card-grid p-6">
                <div class="admin-category-card-grid__full">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">4. SEO metadata</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Search and sharing</h3>
                </div>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}" class="field-input">
                </label>
            </div>
        </div>

        <div class="admin-category-editor__preview space-y-6">
            <div class="surface-card p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Readiness snapshot</p>
                <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Storefront health</h3>

                <div class="mt-6 space-y-4">
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Category card preview</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                            <template x-if="cardImageUrl">
                                <img :src="cardImageUrl" alt="{{ $category->alt_text ?: $category->name }}" class="aspect-[4/3] w-full object-cover">
                            </template>
                            <template x-if="!cardImageUrl">
                                <div class="flex aspect-[4/3] items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Card preview appears after uploading a category image.</div>
                            </template>
                        </div>
                        <p class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="categoryName || 'Category title preview'"></p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]" x-text="storefrontExcerpt || shortDescription || 'Category description preview for browse surfaces.'"></p>
                    </div>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Category hero preview</p>
                        <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                            <div class="relative aspect-[16/7]">
                                <template x-if="bannerImageUrl || mobileBannerUrl">
                                    <img :src="bannerImageUrl || mobileBannerUrl" alt="{{ $category->alt_text ?: $category->name }}" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!bannerImageUrl && !mobileBannerUrl">
                                    <div class="flex h-full items-center justify-center px-6 text-center text-sm text-[var(--color-text-soft)]">Banner preview appears after uploading a hero image.</div>
                                </template>
                                <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,48,73,0.76),rgba(0,48,73,0.18))]"></div>
                                <div class="absolute inset-y-0 left-0 flex max-w-md flex-col justify-center px-6 py-5 text-white">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Category landing</p>
                                    <p class="mt-3 text-2xl font-semibold leading-tight" x-text="categoryName || 'Category title preview'"></p>
                                    <p class="mt-3 text-sm leading-6 text-white/80" x-text="storefrontExcerpt || shortDescription || 'Hero summary preview for category landing pages.'"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Collection preview strip</p>
                        <div class="mt-4 grid gap-3">
                            <template x-for="index in 3" :key="index">
                                <div class="flex items-center gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white px-4 py-3">
                                    <div class="h-14 w-14 overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                        <template x-if="cardImageUrl || iconImageUrl">
                                            <img :src="cardImageUrl || iconImageUrl" alt="" class="h-full w-full object-cover">
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-[var(--color-secondary-900)]" x-text="categoryName || 'Category title preview'"></p>
                                        <p class="mt-1 truncate text-xs text-[var(--color-text-soft)]" x-text="storefrontExcerpt || 'Short category strip summary.'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Checklist</p>
                        <div class="mt-4 space-y-3 text-sm text-[var(--color-text-soft)]">
                            <p>{{ $category->image_url ? 'Category image is ready.' : 'Category image still needed.' }}</p>
                            <p>{{ $category->banner_image_url ? 'Banner image is ready.' : 'Banner image still needed.' }}</p>
                            <p>{{ $category->show_on_homepage ? 'Homepage visibility is enabled.' : 'Homepage visibility is disabled.' }}</p>
                            <p>{{ $category->meta_title || $category->meta_description ? 'SEO metadata has started.' : 'SEO metadata is still empty.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save category changes' : 'Create category' }}</button>
        <a href="{{ route('admin.catalog.categories.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
