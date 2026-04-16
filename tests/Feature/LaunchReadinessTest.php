<?php

use App\Providers\AppServiceProvider;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('serves a robots file with a sitemap reference', function () {
    $this->get(route('seo.robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee(route('seo.sitemap'));
});

it('serves a sitemap with public storefront urls', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('seo.sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('home'), false)
        ->assertSee(route('shop.index'), false)
        ->assertSee('/products/signature-nikah-nama', false);
});

it('renders canonical, robots, and structured data on storefront pages', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('products.show', $product).'">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<script type="application/ld+json">', false)
        ->assertSee('"@type":"Product"', false);
});

it('marks admin pages as noindex', function () {
    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
});

it('recovers gracefully from an object-shaped storefront settings cache payload', function () {
    $this->seed(CatalogSeeder::class);

    Cache::put('storefront.settings', (object) [
        'items' => [
            'announcement_text' => 'Recovered cache payload',
            'support_phone' => '+880 1999-999999',
        ],
    ], now()->addMinutes(10));
    (new AppServiceProvider($this->app))->boot();

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Recovered cache payload');
});
