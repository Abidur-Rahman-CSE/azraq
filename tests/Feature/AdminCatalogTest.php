<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Collection;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use App\Support\MockupZoneNormalizer;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('loads the admin dashboard and catalog pages', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/admin')->assertOk()->assertSee('Catalog admin foundation');
    $this->get('/admin/catalog/categories')->assertOk()->assertSee('Categories');
    $this->get('/admin/catalog/collections')->assertOk()->assertSee('Collections');
    $this->get('/admin/catalog/tags')->assertOk()->assertSee('Tags');
    $this->get('/admin/catalog/products')->assertOk()->assertSee('Products');
});

it('creates a product with a subtype-aware payload', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $category = Category::firstOrFail();
    $related = Product::firstOrFail();

    $response = $this->post('/admin/catalog/products', [
        'category_id' => $category->id,
        'name' => 'Bundle Test Product',
        'type' => ProductType::Bundle->value,
        'status' => 'active',
        'price' => 1999,
        'manage_stock' => true,
        'stock_quantity' => 5,
        'low_stock_threshold' => 1,
        'featured_image_upload' => UploadedFile::fake()->image('featured.jpg'),
        'gallery_uploads' => [
            UploadedFile::fake()->image('gallery-1.jpg'),
            UploadedFile::fake()->image('gallery-2.jpg'),
        ],
        'proof_notes_enabled' => true,
        'font_presets_enabled' => true,
        'personalization_help_text' => 'Use premium proof notes.',
        'collection_ids' => [],
        'tag_ids' => [],
        'related_product_ids' => [$related->id],
        'variants' => [
            [
                'name' => 'Default',
                'sku' => 'BUNDLE-1',
                'option_values' => 'default',
                'price' => 1999,
                'stock_quantity' => 5,
                'is_default' => 1,
            ],
        ],
        'bundle_items' => [
            [
                'child_product_id' => $related->id,
                'quantity' => 2,
            ],
        ],
    ]);

    $response->assertRedirect();

    $product = Product::where('name', 'Bundle Test Product')->first();

    expect($product)->not->toBeNull()
        ->and($product->type)->toBe(ProductType::Bundle)
        ->and($product->variants)->toHaveCount(1)
        ->and($product->bundleItems)->toHaveCount(1)
        ->and($product->featured_image_url)->not->toBeNull()
        ->and($product->images)->toHaveCount(2);
});

it('filters the upgraded products index by product type', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/admin/catalog/products?type='.ProductType::AdvancedPersonalized->value)
        ->assertOk()
        ->assertSee('Signature Nikah Nama')
        ->assertDontSee('Bridal Dupatta');
});

it('creates an advanced personalized product with template and mockup assignments', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $category = Category::where('slug', 'nikah-collection')->firstOrFail();
    $template = PersonalizationTemplate::with('mockups')->firstOrFail();
    $mockups = $template->mockups->take(2)->pluck('id')->all();

    $response = $this->post('/admin/catalog/products', [
        'category_id' => $category->id,
        'name' => 'Editorial Nikah Nama',
        'slug' => 'editorial-nikah-nama',
        'type' => ProductType::AdvancedPersonalized->value,
        'status' => 'active',
        'excerpt' => 'Premium editorial Nikah personalization.',
        'price' => 2990,
        'manage_stock' => false,
        'stock_quantity' => 0,
        'low_stock_threshold' => 0,
        'featured_image_upload' => UploadedFile::fake()->image('featured.jpg'),
        'assigned_template_id' => $template->id,
        'allowed_mockup_ids' => $mockups,
        'default_mockup_id' => $mockups[0],
        'gallery_default_source' => 'template_flat_preview',
        'show_flat_preview_first' => true,
        'include_mockup_gallery' => true,
        'proof_notes_enabled' => true,
        'font_presets_enabled' => true,
        'live_preview_enabled' => true,
        'related_category_ids' => [$category->id],
    ]);

    $response->assertRedirect();

    $product = Product::where('slug', 'editorial-nikah-nama')->firstOrFail();

    expect($product->gallery_default_source)->toBe('template_flat_preview')
        ->and($product->show_flat_preview_first)->toBeTrue()
        ->and($product->include_mockup_gallery)->toBeTrue()
        ->and($product->live_preview_enabled)->toBeTrue()
        ->and($product->personalizationTemplate?->id)->toBe($template->id)
        ->and($product->personalizationMockups()->count())->toBe(2)
        ->and($product->relatedCategories()->count())->toBe(1);
});

it('allows reusable mockups to be assigned from the product form without template matching', function () {
    $this->seed(CatalogSeeder::class);

    $category = Category::where('slug', 'nikah-collection')->firstOrFail();
    $template = PersonalizationTemplate::firstOrFail();
    $mockup = PersonalizationMockup::create([
        'personalization_template_id' => null,
        'title' => 'Universal frame scene',
        'slug' => 'universal-frame-scene',
        'base_image_url' => '/storage/personalization/mockups/universal-frame.jpg',
        'thumb_image_url' => '/storage/personalization/mockups/universal-frame-thumb.jpg',
        'render_mode' => 'perspective_quad',
        'sort_order' => 99,
        'is_active' => true,
    ]);

    $mockup->map()->create([
        'map_type' => 'quad',
        'fit_mode' => 'contain',
        'top_left_x' => 0.20,
        'top_left_y' => 0.18,
        'top_right_x' => 0.80,
        'top_right_y' => 0.18,
        'bottom_right_x' => 0.80,
        'bottom_right_y' => 0.82,
        'bottom_left_x' => 0.20,
        'bottom_left_y' => 0.82,
        'normalized_coordinates' => true,
    ]);

    $response = $this->post('/admin/catalog/products', [
        'category_id' => $category->id,
        'name' => 'Universal Scene Nikah Nama',
        'type' => ProductType::AdvancedPersonalized->value,
        'status' => 'active',
        'price' => 2490,
        'manage_stock' => false,
        'stock_quantity' => 0,
        'low_stock_threshold' => 0,
        'assigned_template_id' => $template->id,
        'allowed_mockup_ids' => [$mockup->id],
        'default_mockup_id' => $mockup->id,
        'related_category_ids' => [$category->id],
    ]);

    $response->assertRedirect();

    $product = Product::where('name', 'Universal Scene Nikah Nama')->firstOrFail();

    expect($product->personalizationTemplate?->id)->toBe($template->id)
        ->and($product->personalizationMockups()->pluck('personalization_mockups.id')->all())->toBe([$mockup->id]);
});

it('shows the nikah personalization setup tools on the advanced product editor', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('nikah-product-form-root', false)
        ->assertSee('nikah-product-form-payload', false)
        ->assertSee('Edit Advanced customization product')
        ->assertSee('Personalization')
        ->assertSee('Signature Nikah Template');
});

it('hydrates normalized mockup map coordinates on the advanced product editor payload', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $mockup = PersonalizationMockup::with('map')->where('slug', 'signature-table-setting')->firstOrFail();
    $normalizedMap = MockupZoneNormalizer::toImageSpace($mockup, $mockup->map);

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee((string) $normalizedMap['top_left_x'], false)
        ->assertSee((string) $normalizedMap['bottom_right_y'], false);
});

it('shows the general product editor with seo fields on create', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.catalog.products.create'))
        ->assertOk()
        ->assertSee('Create General product')
        ->assertSee('General product setup')
        ->assertSee('SEO')
        ->assertSee('Related products')
        ->assertSee('Related categories');
});

it('hydrates variants on the general product editor', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();
    $variant = $product->variants()->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('"variants":[', false)
        ->assertSee($variant->name, false);
});

it('hydrates combo bundle items on the product editor', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'nikkah-combo')->with('bundleItems.childProduct')->firstOrFail();
    $bundleItem = $product->bundleItems->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('Combo')
        ->assertSee('"comboDiscountType"', false)
        ->assertSee('"bundleItems":[', false)
        ->assertSee((string) $bundleItem->child_product_id, false)
        ->assertSee($bundleItem->childProduct->name, false);
});

it('hydrates and updates service booking details on the product editor', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'mehendi-booking')->with('serviceMeta')->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('Service')
        ->assertSee('"serviceMeta":', false)
        ->assertSee('Half day session', false);

    $this->put(route('admin.catalog.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'sku' => $product->sku,
        'type' => ProductType::Service->value,
        'status' => 'active',
        'excerpt' => $product->excerpt,
        'description' => $product->description,
        'price' => $product->price,
        'compare_at_price' => $product->compare_at_price,
        'lead_time_days' => $product->lead_time_days,
        'manage_stock' => false,
        'stock_quantity' => 0,
        'low_stock_threshold' => $product->low_stock_threshold,
        'service_meta' => [
            'service_type' => 'Mehendi',
            'duration_label' => 'Full day booking',
            'location_scope' => 'Dhaka and nearby areas',
            'requires_advance_payment' => true,
            'advance_payment_amount' => 1500,
            'booking_notes' => 'Admin editable package details.',
            'confirmation_note' => 'Availability first, advance after confirmation.',
            'include_items' => json_encode([
                ['title' => 'Bridal mehendi design', 'description' => 'Detailed hands and feet coverage.'],
            ]),
            'faqs' => json_encode([
                ['title' => 'Do you travel?', 'description' => 'Yes, within selected Dhaka areas.'],
            ]),
        ],
    ])->assertRedirect(route('admin.catalog.products.edit', $product));

    $product->refresh();

    expect($product->serviceMeta)
        ->duration_label->toBe('Full day booking')
        ->location_scope->toBe('Dhaka and nearby areas')
        ->booking_notes->toBe('Admin editable package details.')
        ->confirmation_note->toBe('Availability first, advance after confirmation.')
        ->include_items->toHaveCount(1)
        ->faqs->toHaveCount(1);
});

it('supports media uploads on the general product edit form', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false);

    $response = $this->put(route('admin.catalog.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'sku' => $product->sku,
        'type' => ProductType::Standard->value,
        'status' => 'active',
        'excerpt' => $product->excerpt,
        'description' => $product->description,
        'price' => $product->price,
        'compare_at_price' => $product->compare_at_price,
        'lead_time_days' => $product->lead_time_days,
        'manage_stock' => $product->manage_stock,
        'stock_quantity' => $product->stock_quantity,
        'low_stock_threshold' => $product->low_stock_threshold,
        'video_url' => 'https://example.com/product-video',
        'featured_image_upload' => UploadedFile::fake()->image('updated-featured.jpg'),
        'gallery_uploads' => [
            UploadedFile::fake()->image('updated-gallery.jpg'),
        ],
    ]);

    $response->assertRedirect(route('admin.catalog.products.edit', $product));

    $product->refresh();

    expect($product->featured_image_url)->toContain('/storage/products/')
        ->and($product->video_url)->toBe('https://example.com/product-video')
        ->and($product->images()->where('alt_text', $product->name.' gallery image')->exists())->toBeTrue();
});

it('links general product variant option values to saved product images', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->with(['images', 'variants'])->firstOrFail();
    $image = $product->images->firstOrFail();
    $variant = $product->variants->firstOrFail();
    $linkKey = 'option_1:ruby';

    $response = $this->put(route('admin.catalog.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'sku' => $product->sku,
        'type' => ProductType::Standard->value,
        'status' => 'active',
        'price' => $product->price,
        'manage_stock' => $product->manage_stock,
        'stock_quantity' => $product->stock_quantity,
        'low_stock_threshold' => $product->low_stock_threshold,
        'variant_media_links' => json_encode([
            $linkKey => [(string) $image->id],
        ]),
        'variants' => [
            [
                'name' => $variant->name,
                'sku' => $variant->sku,
                'option_values' => implode(', ', $variant->option_values ?? []),
                'price' => $variant->price,
                'stock_quantity' => $variant->stock_quantity,
                'is_default' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.catalog.products.edit', $product));

    $product->refresh();

    expect($product->variant_media_links[$linkKey])->toBe([(string) $image->id]);
});

it('links advanced product variant option values to assigned mockups', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')
        ->with(['personalizationTemplate', 'personalizationMockups', 'variants'])
        ->firstOrFail();
    $mockup = $product->personalizationMockups->firstOrFail();
    $linkKey = 'frame_type:Framed';
    $variant = $product->variants()->create([
        'name' => 'Framed Nikah',
        'sku' => 'AZR-NIK-FRAMED',
        'option_values' => ['frame_type:Framed'],
        'price' => 2900,
        'stock_quantity' => 0,
        'is_default' => true,
        'position' => 0,
    ]);

    $response = $this->put(route('admin.catalog.products.update', $product), [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'sku' => $product->sku,
        'type' => ProductType::AdvancedPersonalized->value,
        'status' => 'active',
        'price' => $product->price,
        'manage_stock' => false,
        'stock_quantity' => 0,
        'low_stock_threshold' => 0,
        'assigned_template_id' => $product->personalizationTemplate->id,
        'allowed_mockup_ids' => $product->personalizationMockups->pluck('id')->all(),
        'default_mockup_id' => $mockup->id,
        'variant_media_links' => json_encode([
            $linkKey => [(string) $mockup->id],
        ]),
        'variants' => [
            [
                'name' => $variant->name,
                'sku' => $variant->sku,
                'option_values' => implode(', ', $variant->option_values ?? []),
                'price' => $variant->price,
                'stock_quantity' => $variant->stock_quantity,
                'is_default' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.catalog.products.edit', $product));

    $product->refresh();

    expect($product->variant_media_links[$linkKey])->toBe([(string) $mockup->id]);
});

it('keeps advanced customization create state clean until a template is chosen', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.catalog.products.create'))
        ->assertOk()
        ->assertSee('"selectedDesignId":""', false)
        ->assertSee('"activeMockupIds":[]', false)
        ->assertSee('"defaultMockupId":""', false);
});

it('creates a category with admin media fields', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $parent = Category::firstOrFail();

    $response = $this->post('/admin/catalog/categories', [
        'parent_id' => $parent->id,
        'name' => 'Signature Frames',
        'slug' => 'signature-frames',
        'description' => 'Category for framed Nikah presentation products.',
        'storefront_excerpt' => 'Short category teaser for browse surfaces.',
        'image_upload' => UploadedFile::fake()->image('category.jpg'),
        'banner_upload' => UploadedFile::fake()->image('banner.jpg', 1600, 700),
        'icon_upload' => UploadedFile::fake()->image('icon.jpg', 400, 400),
        'alt_text' => 'Signature Frames banner',
        'sort_order' => 15,
        'is_active' => true,
        'is_featured' => true,
        'show_on_homepage' => true,
        'related_category_ids' => [$parent->id],
        'meta_title' => 'Signature Frames',
        'meta_description' => 'Frames for premium Nikah presentation.',
    ]);

    $response->assertRedirect();

    $category = Category::where('slug', 'signature-frames')->first();

    expect($category)->not->toBeNull()
        ->and($category->image_url)->not->toBeNull()
        ->and($category->banner_image_url)->not->toBeNull()
        ->and($category->icon_image_url)->not->toBeNull()
        ->and($category->storefront_excerpt)->toBe('Short category teaser for browse surfaces.')
        ->and($category->is_featured)->toBeTrue()
        ->and($category->show_on_homepage)->toBeTrue()
        ->and($category->relatedCategories)->toHaveCount(1);
});

it('creates a collection with cover media and assigned products', function () {
    $this->seed(CatalogSeeder::class);
    Storage::fake('public');

    $products = Product::query()->take(2)->pluck('id')->all();

    $response = $this->post('/admin/catalog/collections', [
        'name' => 'Ceremony Keepsakes',
        'slug' => 'ceremony-keepsakes',
        'description' => 'Curated keepsakes for premium ceremony gifting.',
        'cover_image_upload' => UploadedFile::fake()->image('collection.jpg', 1400, 900),
        'collection_mode' => 'manual',
        'sort_order' => 8,
        'is_active' => true,
        'is_featured' => true,
        'cta_label' => 'Shop keepsakes',
        'product_ids' => $products,
        'meta_title' => 'Ceremony Keepsakes',
        'meta_description' => 'Curated keepsakes collection.',
    ]);

    $response->assertRedirect();

    $collection = Collection::where('slug', 'ceremony-keepsakes')->first();

    expect($collection)->not->toBeNull()
        ->and($collection->cover_image_url)->not->toBeNull()
        ->and($collection->collection_mode)->toBe('manual')
        ->and($collection->is_featured)->toBeTrue()
        ->and($collection->products)->toHaveCount(2);
});
