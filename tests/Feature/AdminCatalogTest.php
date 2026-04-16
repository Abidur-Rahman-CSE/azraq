<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        ->and($product->bundleItems)->toHaveCount(1);
});
