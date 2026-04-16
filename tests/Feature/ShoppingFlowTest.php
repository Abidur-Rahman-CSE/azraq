<?php

use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a standard product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/bridal-dupatta')
        ->assertOk()
        ->assertSee('Bridal Dupatta')
        ->assertSee('Choose a variant')
        ->assertSee('Shipping, care, and policy');
});

it('adds a product to the cart and displays it in the cart page', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'customized-pen')->firstOrFail();
    $variant = $product->variants()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 2,
        'variant_id' => $variant->id,
        'custom_text' => 'A & H',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Customized Pen')
        ->assertSeeText('Custom text: A & H')
        ->assertSee('Subtotal');
});

it('rejects variants that do not belong to the selected product', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'customized-pen')->firstOrFail();
    $wrongVariant = Product::where('slug', 'bridal-dupatta')->firstOrFail()->variants()->firstOrFail();

    $this->from(route('products.show', $product))
        ->post(route('cart.store', $product), [
            'quantity' => 1,
            'variant_id' => $wrongVariant->id,
        ])
        ->assertRedirect(route('products.show', $product))
        ->assertSessionHasErrors('variant_id');

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertDontSee('Customized Pen');
});

it('adds and removes products from the wishlist', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->post(route('wishlist.store', $product))->assertRedirect();

    $this->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee('Bridal Dupatta');

    $this->delete(route('wishlist.destroy', $product))->assertRedirect();

    $this->get(route('wishlist.index'))
        ->assertOk()
        ->assertDontSee('Bridal Dupatta');
});

it('shows the dedicated advanced personalized pdp instead of the standard flow', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/signature-nikah-nama')
        ->assertOk()
        ->assertSee('Add personalized order');
});

it('shows the dedicated light customizable pdp instead of the standard flow', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/customized-pen')
        ->assertOk()
        ->assertSee('Personalize this detail')
        ->assertSee('Add personalized item');
});

it('shows the dedicated bundle pdp instead of redirecting back to the shop page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/nikkah-combo')
        ->assertOk()
        ->assertSee('Everything in this combo')
        ->assertSee('Add full combo');
});
