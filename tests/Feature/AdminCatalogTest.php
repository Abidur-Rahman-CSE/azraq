<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Collection;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
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

it('shows the nikah personalization setup tools on the advanced product editor', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();

    $this->get(route('admin.catalog.products.edit', $product))
        ->assertOk()
        ->assertSee('Nikah personalization and mockup setup')
        ->assertSee('Assigned Personalization Template')
        ->assertSee('Create new template')
        ->assertSee('Open mockup manager')
        ->assertSee('Storefront preview')
        ->assertSee('Flat preview')
        ->assertSee('Mockup preview');
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
