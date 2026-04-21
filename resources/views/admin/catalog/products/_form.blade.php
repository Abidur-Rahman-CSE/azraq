@php
    $isEdit = $product->exists;
    $selectedCollections = collect(old('collection_ids', $product->collections->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedTags = collect(old('tag_ids', $product->tags->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedRelated = collect(old('related_product_ids', $product->relatedProducts->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedRelatedCategories = collect(old('related_category_ids', $product->relatedCategories->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedTemplateId = old('assigned_template_id', $product->personalizationTemplate?->id);
    $selectedMockupIds = collect(old('allowed_mockup_ids', $product->personalizationMockups->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $currentType = old('type', $isEdit ? ($product->type?->value ?? $product->type) : 'advanced_personalized');
    $defaultMockupId = old(
        'default_mockup_id',
        optional($product->personalizationMockups->firstWhere('pivot.is_default', true))->id ?? $product->personalizationMockups->first()?->id
    );
    $preferredNikahCategoryId = old(
        'category_id',
        $product->category_id ?: optional($categories->first(fn ($category) => str_contains(strtolower($category->name.' '.$category->slug), 'nikah')))->id
    );

    $decodedBlueprint = old('personalization_fields_blueprint');

    if (is_string($decodedBlueprint) && filled($decodedBlueprint)) {
        $decodedBlueprint = json_decode($decodedBlueprint, true);
    }

    $personalizationFieldsBlueprint = collect(
        $decodedBlueprint
        ?? $product->personalization_fields_blueprint
        ?? []
    )
        ->map(function ($field) {
            return [
                'id' => $field['id'] ?? null,
                'label' => $field['label'] ?? $field['name'] ?? 'Field',
                'field_key' => $field['field_key'] ?? $field['key'] ?? null,
                'type' => $field['type'] ?? data_get($field, 'settings.input_type'),
                'is_required' => (bool) ($field['is_required'] ?? $field['required'] ?? false),
            ];
        })
        ->values()
        ->all();

    $designPayload = $personalizationTemplates->map(function ($template) {
        return [
            'id' => (int) $template->id,
            'name' => $template->name,
            'thumbnail_url' => $template->thumbnail_image_url ?: $template->preview_image_url ?: $template->base_template_url,
            'preview_url' => $template->preview_image_url ?: $template->base_template_url,
            'fields' => $template->fields->map(function ($field) {
                return [
                    'id' => (int) $field->id,
                    'label' => $field->label,
                    'field_key' => $field->field_key,
                    'is_required' => (bool) $field->is_required,
                    'settings' => $field->settings ?? [],
                ];
            })->values()->all(),
            'mockups' => $template->mockups->map(function ($mockup) {
                return [
                    'id' => (int) $mockup->id,
                    'title' => $mockup->title,
                    'thumb_image_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                    'base_image_url' => $mockup->base_image_url,
                    'overlay_image_url' => $mockup->overlay_image_url,
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
                ];
            })->values()->all(),
        ];
    })->values();

    $formPayload = [
        'page' => [
            'heading' => $isEdit ? 'Edit Nikahnama product' : 'Create Nikahnama product',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => route('admin.dashboard')],
                ['label' => 'Catalog'],
                ['label' => 'Products', 'href' => route('admin.catalog.products.index')],
                ['label' => $isEdit ? $product->name : 'Create'],
            ],
        ],
        'product' => [
            'name' => old('name', $product->name),
            'excerpt' => old('excerpt', $product->excerpt),
            'categoryId' => $preferredNikahCategoryId ? (string) $preferredNikahCategoryId : '',
            'tagIds' => $selectedTags,
            'relatedProductIds' => $selectedRelated,
            'relatedCategoryIds' => $selectedRelatedCategories,
            'status' => old('status', $product->status ?: 'draft'),
            'currentType' => $currentType,
            'price' => (string) old('price', $product->price),
            'compareAtPrice' => (string) old('compare_at_price', $product->compare_at_price),
            'selectedDesignId' => $selectedTemplateId ? (int) $selectedTemplateId : '',
            'activeMockupIds' => $selectedMockupIds,
            'defaultMockupId' => $defaultMockupId ? (int) $defaultMockupId : '',
            'personalizationFields' => $personalizationFieldsBlueprint,
        ],
        'categories' => $categories->map(fn ($category) => [
            'id' => (string) $category->id,
            'name' => $category->name,
        ])->values()->all(),
        'relatedCategories' => $relatedCategories->map(fn ($category) => [
            'id' => (int) $category->id,
            'name' => $category->name,
        ])->values()->all(),
        'tags' => $tags->map(fn ($tag) => [
            'id' => (int) $tag->id,
            'name' => $tag->name,
        ])->values()->all(),
        'relatedProducts' => $relatedProducts->map(fn ($relatedProduct) => [
            'id' => (int) $relatedProduct->id,
            'name' => $relatedProduct->name,
        ])->values()->all(),
        'designs' => $designPayload->all(),
        'errors' => $errors->toArray(),
    ];
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('admin.catalog.products.update', $product) : route('admin.catalog.products.store') }}"
    class="space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @foreach ($selectedCollections as $collectionId)
        <input type="hidden" name="collection_ids[]" value="{{ $collectionId }}">
    @endforeach

    <div id="nikah-product-form-root"></div>
    <script type="application/json" id="nikah-product-form-payload">@json($formPayload)</script>

    <noscript>
        <div class="rounded-xl border border-[var(--color-border-soft)] bg-white px-5 py-4 text-sm text-[var(--color-text-soft)]">
            This editor needs JavaScript enabled in the admin workspace.
        </div>
    </noscript>
</form>
