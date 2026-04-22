<?php

use App\Models\PersonalizationTemplate;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Support\MockupZoneNormalizer;
use App\Models\Category;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows the advanced personalized product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/signature-nikah-nama')
        ->assertOk()
        ->assertSee('Signature Nikah Nama')
        ->assertSee('Preview gallery')
        ->assertSee('Flat certificate preview')
        ->assertSee('Signature table setting')
        ->assertSee('Choose a font')
        ->assertSee('Bride Name')
        ->assertSee('Add personalized order');
});

it('adds an advanced personalized product to the cart with structured payload data', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::with(['fields', 'fonts'])->whereBelongsTo($product)->firstOrFail();

    $response = $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $template->fonts->first()->id,
        'proof_note' => 'Please keep the bride name slightly larger.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
    ]);

    $response->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSeeText('Bride Name: Amena')
        ->assertSeeText('Groom Name: Hassan')
        ->assertSeeText('Proof note: Please keep the bride name slightly larger.');
});

it('loads the admin personalization template manager', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.personalization.templates.index'))
        ->assertOk()
        ->assertSee('Template manager')
        ->assertSee('Signature Nikah Template');
});

it('duplicates a personalization template with its fields and fonts', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();

    $response = $this->post(route('admin.personalization.templates.duplicate', $template));

    $duplicate = PersonalizationTemplate::where('name', $template->name.' Copy')->latest('id')->first();

    $response->assertRedirect(route('admin.personalization.templates.edit', $duplicate));

    expect($duplicate)->not->toBeNull()
        ->and($duplicate->id)->not->toBe($template->id)
        ->and($duplicate->product_id)->toBeNull()
        ->and($duplicate->base_template_url)->toBe($template->base_template_url)
        ->and($duplicate->preview_image_url)->toBe($template->preview_image_url)
        ->and($duplicate->mask_image_url)->toBe($template->mask_image_url)
        ->and($duplicate->is_active)->toBeFalse()
        ->and($duplicate->thumbnail_image_url)->toContain('/storage/personalization/templates/snapshots/');

    $duplicate->load(['fields', 'fonts']);

    expect($duplicate->fields)->toHaveCount($template->fields->count())
        ->and($duplicate->fonts)->toHaveCount($template->fonts->count())
        ->and($duplicate->fields->first()->field_key)->toBe($template->fields->first()->field_key)
        ->and($duplicate->fonts->first()->name)->toBe($template->fonts->first()->name);
});

it('updates a duplicated personalization template without requiring an assigned product', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();

    $this->post(route('admin.personalization.templates.duplicate', $template));

    $duplicate = PersonalizationTemplate::with(['fields', 'fonts'])
        ->where('name', $template->name.' Copy')
        ->latest('id')
        ->firstOrFail();

    $response = $this->put(route('admin.personalization.templates.update', $duplicate), [
        'product_id' => '',
        'name' => 'Signature Nikah Template Copy Revised',
        'base_template_url' => $duplicate->base_template_url,
        'preview_image_url' => $duplicate->preview_image_url,
        'mask_image_url' => $duplicate->mask_image_url,
        'export_ratio_width' => $duplicate->export_ratio_width,
        'export_ratio_height' => $duplicate->export_ratio_height,
        'instructions' => $duplicate->instructions,
        'safe_zone_notes' => $duplicate->safe_zone_notes,
        'proof_note_label' => $duplicate->proof_note_label,
        'is_active' => 0,
        'preview_rules' => $duplicate->preview_rules,
        'render_rules' => $duplicate->render_rules,
        'preview_data_presets' => $duplicate->preview_data_presets,
        'fields_payload' => json_encode($duplicate->fields->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'preview_sample_value' => $field->preview_sample_value,
            'is_required' => $field->is_required ? 1 : 0,
            'min_length' => $field->min_length,
            'max_length' => $field->max_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'settings' => $field->settings,
        ])->values()->all()),
        'fonts_payload' => json_encode($duplicate->fonts->map(fn ($font) => [
            'name' => $font->name,
            'internal_name' => $font->internal_name,
            'preview_label' => $font->preview_label,
            'css_font_family' => $font->css_font_family,
            'font_family' => $font->font_family,
            'font_source_type' => $font->font_source_type,
            'font_source_value' => $font->font_source_value,
            'category' => $font->category,
            'style_type' => $font->style_type,
            'supported_use' => $font->supported_use,
            'preview_sample_text' => $font->preview_sample_text,
            'font_weight_default' => $font->font_weight_default,
            'font_style_default' => $font->font_style_default,
            'letter_spacing_default' => $font->letter_spacing_default,
            'line_height_default' => $font->line_height_default,
            'text_transform_default' => $font->text_transform_default,
            'recommended_for' => $font->recommended_for,
            'is_default' => $font->is_default ? 1 : 0,
            'is_active' => $font->is_active ? 1 : 0,
            'sort_order' => $font->sort_order,
        ])->values()->all()),
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $duplicate));

    $duplicate->refresh();

    expect($duplicate->product_id)->toBeNull()
        ->and($duplicate->name)->toBe('Signature Nikah Template Copy Revised');
});

it('loads the admin mockup manager with seeded Nikah mockups', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.mockups.index'))
        ->assertOk()
        ->assertSee('Lifestyle mockups')
        ->assertSee('Signature table setting')
        ->assertSee('Ceremony desk lifestyle');

    expect(PersonalizationMockup::count())->toBe(3);
});

it('hydrates existing mockup assets and saved map coordinates on the edit page', function () {
    $this->seed(CatalogSeeder::class);

    $mockup = PersonalizationMockup::with('map')->where('slug', 'signature-table-setting')->firstOrFail();
    $normalizedMap = MockupZoneNormalizer::toImageSpace($mockup, $mockup->map);

    $this->get(route('admin.mockups.edit', $mockup))
        ->assertOk()
        ->assertSee($mockup->base_image_url, false)
        ->assertSee((string) $normalizedMap['top_left_x'], false)
        ->assertSee((string) $normalizedMap['top_right_y'], false)
        ->assertSee((string) $normalizedMap['bottom_right_y'], false)
        ->assertSee((string) $normalizedMap['bottom_left_x'], false);
});

it('creates a mockup from the admin editor and saves normalized map data', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();

    $response = $this->post(route('admin.mockups.store'), [
        'personalization_template_id' => $template->id,
        'title' => 'Warm desk preview',
        'render_mode' => 'perspective_quad',
        'sort_order' => 9,
        'is_active' => 1,
        'base_image_upload' => UploadedFile::fake()->image('base.jpg', 1600, 1200),
        'mask_image_upload' => UploadedFile::fake()->image('mask.png', 1600, 1200),
        'overlay_image_upload' => UploadedFile::fake()->image('overlay.png', 1600, 1200),
        'thumb_image_upload' => UploadedFile::fake()->image('thumb.jpg', 800, 600),
        'notes' => 'Warm editorial desk scene.',
        'map' => [
            'map_type' => 'quad',
            'fit_mode' => 'contain',
            'top_left_x' => 0.21,
            'top_left_y' => 0.19,
            'top_right_x' => 0.79,
            'top_right_y' => 0.18,
            'bottom_right_x' => 0.82,
            'bottom_right_y' => 0.81,
            'bottom_left_x' => 0.19,
            'bottom_left_y' => 0.82,
            'manual_rotation' => -1.25,
            'shadow_strength' => 0.22,
            'highlight_strength' => 0.14,
            'opacity' => 0.94,
        ],
    ]);

    $response->assertRedirect();

    $mockup = PersonalizationMockup::where('title', 'Warm desk preview')->first();

    expect($mockup)->not->toBeNull()
        ->and($mockup->base_image_url)->not->toBeNull()
        ->and($mockup->map)->not->toBeNull()
        ->and((float) $mockup->map->top_left_x)->toEqual(0.21)
        ->and((float) $mockup->map->bottom_right_y)->toEqual(0.81);
});

it('creates a reusable mockup without assigning a personalization template', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $response = $this->post(route('admin.mockups.store'), [
        'title' => 'Universal reusable mockup',
        'render_mode' => 'perspective_quad',
        'sort_order' => 12,
        'is_active' => 1,
        'base_image_upload' => UploadedFile::fake()->image('base.jpg', 1600, 1200),
        'thumb_image_upload' => UploadedFile::fake()->image('thumb.jpg', 800, 600),
        'map' => [
            'map_type' => 'quad',
            'fit_mode' => 'contain',
            'top_left_x' => 0.21,
            'top_left_y' => 0.19,
            'top_right_x' => 0.79,
            'top_right_y' => 0.18,
            'bottom_right_x' => 0.82,
            'bottom_right_y' => 0.81,
            'bottom_left_x' => 0.19,
            'bottom_left_y' => 0.82,
        ],
    ]);

    $response->assertRedirect();

    $mockup = PersonalizationMockup::where('title', 'Universal reusable mockup')->firstOrFail();

    expect($mockup->personalization_template_id)->toBeNull()
        ->and($mockup->map)->not->toBeNull();
});

it('requires a base image before creating a mockup', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::firstOrFail();

    $response = $this->from(route('admin.mockups.create'))->post(route('admin.mockups.store'), [
        'personalization_template_id' => $template->id,
        'title' => 'Incomplete mockup',
        'render_mode' => 'perspective_quad',
        'sort_order' => 3,
        'is_active' => 1,
        'map' => [
            'map_type' => 'quad',
            'fit_mode' => 'contain',
            'top_left_x' => 0.21,
            'top_left_y' => 0.19,
            'top_right_x' => 0.79,
            'top_right_y' => 0.18,
            'bottom_right_x' => 0.82,
            'bottom_right_y' => 0.81,
            'bottom_left_x' => 0.19,
            'bottom_left_y' => 0.82,
        ],
    ]);

    $response->assertRedirect(route('admin.mockups.create'))
        ->assertSessionHasErrors('base_image_upload');

    expect(PersonalizationMockup::where('title', 'Incomplete mockup')->exists())->toBeFalse();
});

it('creates an upgraded personalization template with uploaded assets and fields', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $category = Category::where('slug', 'nikah-collection')->firstOrFail();
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Template Test Product',
        'slug' => 'template-test-product',
        'sku' => 'AZR-TPL-TEST',
        'type' => \App\Enums\ProductType::AdvancedPersonalized,
        'status' => 'active',
        'price' => 1900,
        'manage_stock' => false,
        'stock_quantity' => 0,
        'low_stock_threshold' => 0,
    ]);

    $response = $this->post(route('admin.personalization.templates.store'), [
        'product_id' => $product->id,
        'name' => 'Premium Nikah Template',
        'base_template_upload' => UploadedFile::fake()->image('base.jpg', 1400, 2000),
        'preview_image_upload' => UploadedFile::fake()->image('preview.jpg', 1400, 900),
        'mask_image_upload' => UploadedFile::fake()->image('mask.png', 1400, 2000),
        'instructions' => 'Keep the main seal area clear.',
        'safe_zone_notes' => 'Avoid the outer frame edge.',
        'proof_note_label' => 'Designer proof notes',
        'preview_rules' => [
            'safe_scale' => 1,
            'allow_multiline' => 1,
        ],
        'render_rules' => [
            'export_format' => 'png',
            'proof_required' => 1,
        ],
        'preview_data_presets' => [
            'bride_name' => 'Sara',
            'groom_name' => 'Imran',
            'ceremony_date' => '01 January 2027',
            'venue' => 'Dhaka',
        ],
        'fields' => [
            [
                'label' => 'Bride Name',
                'field_key' => 'bride_name',
                'placeholder' => 'Enter bride name',
                'help_text' => 'Main bride name zone',
                'default_value' => 'Sara',
                'is_required' => 1,
                'max_length' => 60,
                'min_length' => 2,
                'font_size_min' => 20,
                'font_size_max' => 42,
                'line_height' => 1.2,
                'letter_spacing' => 0,
                'text_align' => 'center',
                'text_color' => '#780000',
                'position_x' => 50,
                'position_y' => 34,
                'width' => 70,
                'height' => 10,
                'rotation' => 0,
            ],
        ],
        'fonts' => [
            [
                'name' => 'Classic Serif',
                'css_font_family' => 'Cormorant Garamond, serif',
                'preview_label' => 'Classic Serif',
                'is_default' => 1,
            ],
        ],
        'is_active' => 1,
    ]);

    $response->assertRedirect();

    $template = PersonalizationTemplate::where('name', 'Premium Nikah Template')->first();

    expect($template)->not->toBeNull()
        ->and($template->base_template_url)->not->toBeNull()
        ->and($template->preview_image_url)->not->toBeNull()
        ->and($template->mask_image_url)->not->toBeNull()
        ->and($template->preview_data_presets['bride_name'])->toBe('Sara')
        ->and($template->fields)->toHaveCount(1)
        ->and($template->fonts)->toHaveCount(1);
});

it('requires a base template image before saving a personalization template', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::firstOrFail();

    $response = $this->from(route('admin.personalization.templates.edit', $template))
        ->put(route('admin.personalization.templates.update', $template), [
            'product_id' => $template->product_id,
            'name' => $template->name,
            'remove_base_template' => 1,
            'preview_data_presets' => [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
                'ceremony_date' => '12 December 2026',
                'venue' => 'Dhaka',
            ],
            'fields' => [
                [
                    'label' => 'Bride Name',
                    'field_key' => 'bride_name',
                ],
            ],
            'fonts' => [
                [
                    'name' => 'Classic Serif',
                    'css_font_family' => 'Cormorant Garamond, serif',
                    'preview_label' => 'Classic Serif',
                    'is_default' => 1,
                ],
            ],
        ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template))
        ->assertSessionHasErrors('base_template_upload');
});

it('updates an existing personalization template with a newly uploaded base image', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();
    $originalBaseUrl = $template->base_template_url;

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'base_template_upload' => UploadedFile::fake()->image('replacement-base.jpg', 1400, 2000),
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
        'fields' => collect($template->fields)->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'is_required' => $field->is_required ? 1 : 0,
            'max_length' => $field->max_length,
            'min_length' => $field->min_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'preview_sample_value' => $field->preview_sample_value,
        ])->values()->toArray(),
        'fonts' => collect($template->fonts)->map(fn ($font) => [
            'name' => $font->name,
            'css_font_family' => $font->css_font_family,
            'preview_label' => $font->preview_label,
            'is_default' => $font->is_default ? 1 : 0,
        ])->values()->toArray(),
        'save_mode' => 'template',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh();

    expect($template->base_template_url)
        ->not->toBe($originalBaseUrl)
        ->and($template->base_template_url)->toContain('/storage/personalization/templates/')
        ->and($template->thumbnail_image_url)->toContain('/storage/personalization/templates/snapshots/');
});

it('generates a clean snapshot thumbnail for the personalization template list', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'save_mode' => 'template',
        'is_active' => 1,
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => $template->preview_data_presets,
        'fields_payload' => json_encode($template->fields->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'preview_sample_value' => $field->preview_sample_value,
            'is_required' => $field->is_required ? 1 : 0,
            'max_length' => $field->max_length,
            'min_length' => $field->min_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'settings' => $field->settings,
        ])->values()->all(), JSON_THROW_ON_ERROR),
        'fonts_payload' => json_encode($template->fonts->map(fn ($font) => [
            'name' => $font->name,
            'internal_name' => $font->internal_name,
            'preview_label' => $font->preview_label,
            'css_font_family' => $font->css_font_family,
            'font_family' => $font->font_family,
            'font_source_type' => $font->font_source_type,
            'font_source_value' => $font->font_source_value,
            'category' => $font->category,
            'style_type' => $font->style_type,
            'supported_use' => $font->supported_use,
            'preview_sample_text' => $font->preview_sample_text,
            'font_weight_default' => $font->font_weight_default,
            'font_style_default' => $font->font_style_default,
            'letter_spacing_default' => $font->letter_spacing_default,
            'line_height_default' => $font->line_height_default,
            'text_transform_default' => $font->text_transform_default,
            'recommended_for' => $font->recommended_for,
            'is_default' => $font->is_default ? 1 : 0,
            'is_active' => $font->is_active ? 1 : 0,
            'sort_order' => $font->sort_order,
        ])->values()->all(), JSON_THROW_ON_ERROR),
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh();

    expect($template->thumbnail_image_url)
        ->not->toBeNull()
        ->and($template->thumbnail_image_url)->toContain('/storage/personalization/templates/snapshots/');

    $snapshotPath = str($template->thumbnail_image_url)->after('/storage/')->toString();
    $snapshotMarkup = Storage::disk('public')->get($snapshotPath);

    expect($snapshotMarkup)
        ->toContain('data:image/')
        ->and($snapshotMarkup)->toContain('<image href="data:image/');

    $this->get(route('admin.personalization.templates.index'))
        ->assertOk()
        ->assertSee($template->thumbnail_image_url, false);
});

it('does not delete a shared managed asset when a template image is replaced', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    Storage::disk('public')->put('personalization/shared/coupled-asset.png', 'shared-asset');

    $sharedUrl = Storage::url('personalization/shared/coupled-asset.png');
    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();
    $mockup = PersonalizationMockup::with('map')->firstOrFail();

    $template->update(['base_template_url' => $sharedUrl]);
    $mockup->update(['base_image_url' => $sharedUrl]);

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'base_template_upload' => UploadedFile::fake()->image('replacement-base.jpg', 1400, 2000),
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
        'fields_payload' => json_encode($template->fields->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'preview_sample_value' => $field->preview_sample_value,
            'is_required' => $field->is_required ? 1 : 0,
            'max_length' => $field->max_length,
            'min_length' => $field->min_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'settings' => $field->settings,
        ])->values()->all(), JSON_THROW_ON_ERROR),
        'fonts_payload' => json_encode($template->fonts->map(fn ($font) => [
            'name' => $font->name,
            'internal_name' => $font->internal_name,
            'preview_label' => $font->preview_label,
            'css_font_family' => $font->css_font_family,
            'font_family' => $font->font_family,
            'font_source_type' => $font->font_source_type,
            'font_source_value' => $font->font_source_value,
            'category' => $font->category,
            'style_type' => $font->style_type,
            'supported_use' => $font->supported_use,
            'preview_sample_text' => $font->preview_sample_text,
            'font_weight_default' => $font->font_weight_default,
            'font_style_default' => $font->font_style_default,
            'letter_spacing_default' => $font->letter_spacing_default,
            'line_height_default' => $font->line_height_default,
            'text_transform_default' => $font->text_transform_default,
            'recommended_for' => $font->recommended_for,
            'is_default' => $font->is_default ? 1 : 0,
            'is_active' => $font->is_active ? 1 : 0,
            'sort_order' => $font->sort_order,
        ])->values()->all(), JSON_THROW_ON_ERROR),
        'save_mode' => 'template',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh();
    $mockup->refresh();

    expect($template->base_template_url)->not->toBe($sharedUrl)
        ->and($mockup->base_image_url)->toBe($sharedUrl)
        ->and(Storage::disk('public')->exists('personalization/shared/coupled-asset.png'))->toBeTrue();
});

it('does not delete a shared managed asset when a mockup image is replaced', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    Storage::disk('public')->put('personalization/shared/shared-scene.png', 'shared-scene');

    $sharedUrl = Storage::url('personalization/shared/shared-scene.png');
    $template = PersonalizationTemplate::firstOrFail();
    $mockup = PersonalizationMockup::with('map')->firstOrFail();

    $template->update(['preview_image_url' => $sharedUrl]);
    $mockup->update(['base_image_url' => $sharedUrl]);

    $response = $this->put(route('admin.mockups.update', $mockup), [
        'personalization_template_id' => $mockup->personalization_template_id,
        'title' => $mockup->title,
        'slug' => $mockup->slug,
        'render_mode' => $mockup->render_mode,
        'sort_order' => $mockup->sort_order,
        'is_active' => $mockup->is_active ? 1 : 0,
        'base_image_upload' => UploadedFile::fake()->image('new-scene.jpg', 1600, 1200),
        'base_image_url' => $mockup->base_image_url,
        'mask_image_url' => $mockup->mask_image_url,
        'overlay_image_url' => $mockup->overlay_image_url,
        'thumb_image_url' => $mockup->thumb_image_url,
        'notes' => $mockup->notes,
        'map' => [
            'map_type' => $mockup->map->map_type,
            'fit_mode' => $mockup->map->fit_mode,
            'top_left_x' => $mockup->map->top_left_x,
            'top_left_y' => $mockup->map->top_left_y,
            'top_right_x' => $mockup->map->top_right_x,
            'top_right_y' => $mockup->map->top_right_y,
            'bottom_right_x' => $mockup->map->bottom_right_x,
            'bottom_right_y' => $mockup->map->bottom_right_y,
            'bottom_left_x' => $mockup->map->bottom_left_x,
            'bottom_left_y' => $mockup->map->bottom_left_y,
            'manual_rotation' => $mockup->map->manual_rotation,
            'shadow_strength' => $mockup->map->shadow_strength,
            'highlight_strength' => $mockup->map->highlight_strength,
            'opacity' => $mockup->map->opacity,
        ],
    ]);

    $response->assertRedirect(route('admin.mockups.edit', $mockup));

    $template->refresh();
    $mockup->refresh();

    expect($mockup->base_image_url)->not->toBe($sharedUrl)
        ->and($template->preview_image_url)->toBe($sharedUrl)
        ->and(Storage::disk('public')->exists('personalization/shared/shared-scene.png'))->toBeTrue();
});

it('persists field text and typography updates without needing canvas movement', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();
    $field = $template->fields->firstOrFail();

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'save_mode' => 'template',
        'is_active' => 1,
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Nusrat',
            'groom_name' => 'Rahim',
            'ceremony_date' => '1 January 2027',
            'venue' => 'Dhaka Club',
        ],
        'fields_payload' => json_encode([
            [
                'label' => 'Bride full name',
                'field_key' => $field->field_key,
                'placeholder' => $field->placeholder,
                'help_text' => 'Shown on the main line',
                'default_value' => 'Nusrat Jahan',
                'preview_sample_value' => 'Nusrat Jahan',
                'is_required' => 1,
                'max_length' => $field->max_length,
                'min_length' => $field->min_length,
                'font_size_min' => 14,
                'font_size_max' => 20,
                'line_height' => 1.1,
                'letter_spacing' => 0,
                'text_align' => 'center',
                'text_color' => '#780000',
                'position_x' => $field->position_x,
                'position_y' => $field->position_y,
                'width' => $field->width,
                'height' => $field->height,
                'rotation' => $field->rotation,
                'z_index' => $field->z_index,
                'settings' => [
                    'auto_fit' => 1,
                    'allow_multiline' => 0,
                    'max_lines' => 1,
                    'overflow_behavior' => 'shrink_only',
                    'font_family_override' => '"Poppins", sans-serif',
                    'font_weight' => '700',
                    'text_transform' => 'uppercase',
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        'fonts_payload' => json_encode($template->fonts->map(fn ($font) => [
            'name' => $font->name,
            'preview_label' => $font->preview_label,
            'css_font_family' => $font->css_font_family,
            'is_default' => $font->is_default ? 1 : 0,
        ])->values()->all(), JSON_THROW_ON_ERROR),
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh()->load('fields');
    $updatedField = $template->fields->firstOrFail();

    expect($updatedField->label)->toBe('Bride full name')
        ->and($updatedField->preview_sample_value)->toBe('Nusrat Jahan')
        ->and($updatedField->font_size_min)->toBe(14)
        ->and($updatedField->font_size_max)->toBe(20)
        ->and($updatedField->settings['overflow_behavior'])->toBe('shrink_only')
        ->and($updatedField->settings['text_transform'])->toBe('uppercase');
});

it('persists shrink-only fitting rules for template fields', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();
    $field = $template->fields->firstOrFail();

    $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'save_mode' => 'template',
        'is_active' => 1,
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Farzana Akter',
            'groom_name' => 'Md Sakib',
            'ceremony_date' => '15 February 2027',
            'venue' => 'Gulshan, Dhaka',
        ],
        'fields_payload' => json_encode([
            [
                'label' => $field->label,
                'field_key' => $field->field_key,
                'placeholder' => $field->placeholder,
                'help_text' => $field->help_text,
                'default_value' => $field->default_value,
                'preview_sample_value' => 'A very long single line certificate value',
                'is_required' => $field->is_required ? 1 : 0,
                'max_length' => $field->max_length,
                'min_length' => $field->min_length,
                'font_size_min' => 12,
                'font_size_max' => 18,
                'line_height' => 1.0,
                'letter_spacing' => 0,
                'text_align' => 'center',
                'text_color' => '#780000',
                'position_x' => $field->position_x,
                'position_y' => $field->position_y,
                'width' => 40,
                'height' => 10,
                'rotation' => $field->rotation,
                'z_index' => $field->z_index,
                'settings' => [
                    'auto_fit' => 1,
                    'allow_multiline' => 1,
                    'max_lines' => 3,
                    'overflow_behavior' => 'shrink_only',
                    'font_family_override' => '',
                    'font_weight' => '600',
                    'text_transform' => 'none',
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        'fonts_payload' => json_encode($template->fonts->map(fn ($font) => [
            'name' => $font->name,
            'preview_label' => $font->preview_label,
            'css_font_family' => $font->css_font_family,
            'is_default' => $font->is_default ? 1 : 0,
        ])->values()->all(), JSON_THROW_ON_ERROR),
    ])->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh()->load('fields');
    $updatedField = $template->fields->firstOrFail();

    expect($updatedField->settings['overflow_behavior'])->toBe('shrink_only')
        ->and($updatedField->settings['allow_multiline'])->toBeTrue()
        ->and($updatedField->font_size_min)->toBe(12)
        ->and($updatedField->font_size_max)->toBe(18);
});

it('persists rich typography preset metadata for a template', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::with(['fields', 'fonts'])->firstOrFail();

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name,
        'save_mode' => 'template',
        'is_active' => 1,
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
        'fields_payload' => json_encode($template->fields->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'preview_sample_value' => $field->preview_sample_value,
            'is_required' => $field->is_required ? 1 : 0,
            'max_length' => $field->max_length,
            'min_length' => $field->min_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'settings' => $field->settings,
        ])->values()->all(), JSON_THROW_ON_ERROR),
        'fonts_payload' => json_encode([
            [
                'name' => 'Royal Script',
                'internal_name' => 'royal_script',
                'preview_label' => 'Royal Script',
                'css_font_family' => '"Allura", cursive',
                'font_family' => '"Allura", cursive',
                'font_source_type' => 'google',
                'font_source_value' => 'https://fonts.googleapis.com/css2?family=Allura&display=swap',
                'category' => 'Signature Script',
                'style_type' => 'Luxury Calligraphy',
                'supported_use' => 'all',
                'preview_sample_text' => 'Amena & Hassan',
                'font_weight_default' => '600',
                'font_style_default' => 'normal',
                'letter_spacing_default' => 0.2,
                'line_height_default' => 1.25,
                'text_transform_default' => 'none',
                'recommended_for' => 'bride_name,groom_name',
                'is_default' => 1,
                'is_active' => 1,
                'sort_order' => 0,
            ],
            [
                'name' => 'Formal Roman',
                'internal_name' => 'formal_roman',
                'preview_label' => 'Formal Roman',
                'css_font_family' => '"Cinzel", serif',
                'font_family' => '"Cinzel", serif',
                'font_source_type' => 'google',
                'font_source_value' => 'https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&display=swap',
                'category' => 'Formal Roman',
                'style_type' => 'Formal Roman',
                'supported_use' => 'all',
                'preview_sample_text' => 'Nikah Nama',
                'font_weight_default' => '700',
                'font_style_default' => 'normal',
                'letter_spacing_default' => 0.8,
                'line_height_default' => 1.2,
                'text_transform_default' => 'uppercase',
                'recommended_for' => 'date,venue,all',
                'is_default' => 0,
                'is_active' => 0,
                'sort_order' => 10,
            ],
        ], JSON_THROW_ON_ERROR),
    ]);

    $response->assertRedirect(route('admin.personalization.templates.edit', $template));

    $template->refresh()->load('fonts');
    $font = $template->fonts->firstWhere('internal_name', 'royal_script');
    $inactive = $template->fonts->firstWhere('internal_name', 'formal_roman');

    expect($font)->not->toBeNull()
        ->and($font->font_source_type)->toBe('google')
        ->and($font->category)->toBe('Signature Script')
        ->and($font->recommended_for)->toBe('bride_name,groom_name')
        ->and($font->is_active)->toBeTrue()
        ->and($inactive)->not->toBeNull()
        ->and($inactive->is_active)->toBeFalse();
});

it('saves a draft without unpublishing an active personalization template', function () {
    $this->seed(CatalogSeeder::class);

    $template = PersonalizationTemplate::firstOrFail();
    $template->update(['is_active' => true]);

    $response = $this->put(route('admin.personalization.templates.update', $template), [
        'product_id' => $template->product_id,
        'name' => $template->name.' Draft Check',
        'save_mode' => 'draft',
        'is_active' => 1,
        'base_template_url' => $template->base_template_url,
        'preview_image_url' => $template->preview_image_url,
        'mask_image_url' => $template->mask_image_url,
        'preview_data_presets' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
        'fields' => collect($template->fields)->map(fn ($field) => [
            'label' => $field->label,
            'field_key' => $field->field_key,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'default_value' => $field->default_value,
            'is_required' => $field->is_required ? 1 : 0,
            'max_length' => $field->max_length,
            'min_length' => $field->min_length,
            'font_size_min' => $field->font_size_min,
            'font_size_max' => $field->font_size_max,
            'line_height' => $field->line_height,
            'letter_spacing' => $field->letter_spacing,
            'text_align' => $field->text_align,
            'text_color' => $field->text_color,
            'position_x' => $field->position_x,
            'position_y' => $field->position_y,
            'width' => $field->width,
            'height' => $field->height,
            'rotation' => $field->rotation,
            'z_index' => $field->z_index,
            'preview_sample_value' => $field->preview_sample_value,
        ])->values()->toArray(),
        'fonts' => collect($template->fonts)->map(fn ($font) => [
            'name' => $font->name,
            'css_font_family' => $font->css_font_family,
            'preview_label' => $font->preview_label,
            'is_default' => $font->is_default ? 1 : 0,
        ])->values()->toArray(),
    ]);

    expect($response->status())->toBe(302)
        ->and($response->headers->get('Location'))->toBe(route('admin.personalization.templates.edit', $template))
        ->and($response->baseResponse->getSession()->get('status'))->toBe('Template draft saved without changing live status.');

    $template->refresh();

    expect($template->is_active)->toBeTrue()
        ->and($template->name)->toContain('Draft Check');
});
