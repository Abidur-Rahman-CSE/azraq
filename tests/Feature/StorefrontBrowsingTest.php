<?php

use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the storefront home with featured catalog content', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/')
        ->assertOk()
        ->assertSee('A premium bridal storefront with configurable homepage sections.')
        ->assertSee('Featured categories')
        ->assertSee('Featured collections');
});

it('renders the storefront header with desktop navigation and a mobile drawer', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/')
        ->assertOk()
        ->assertSee('class="container-shell header-shell flex items-center justify-between gap-4 py-4 lg:grid', false)
        ->assertSee('class="header-icon-button lg:hidden"', false)
        ->assertSee('class="hidden items-center justify-center gap-5 xl:flex', false)
        ->assertSee('class="header-icon-button hidden lg:inline-flex" aria-label="Wishlist"', false)
        ->assertSee('class="button-primary hidden 2xl:inline-flex"', false)
        ->assertSee('id="mobile-navigation-drawer"', false)
        ->assertSee('class="mobile-drawer-cta"', false);
});

it('shows the shop index and supports filtering by product type', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/shop?type=advanced_personalized')
        ->assertOk()
        ->assertSee('Shop')
        ->assertSee('Signature Nikah Nama')
        ->assertDontSee('Bridal Dupatta');
});

it('shows category pages', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/categories/nikah-collection')
        ->assertOk()
        ->assertSee('Nikah Collection')
        ->assertSee('Signature Nikah Nama')
        ->assertSee('Customized Pen');
});

it('shows collection pages', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/collections/signature-nikah')
        ->assertOk()
        ->assertSee('Signature Nikah')
        ->assertSee('Signature Nikah Nama');
});

it('shows a dedicated search results page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/search?search=nikah')
        ->assertOk()
        ->assertSee('Results for “nikah”')
        ->assertSee('Signature Nikah Nama');
});
