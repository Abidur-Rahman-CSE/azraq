<?php

use App\Models\Product;
use App\Models\PersonalizationTemplate;
use App\Support\ComboPricing;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a standard product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/bridal-dupatta')
        ->assertOk()
        ->assertSee('Bridal Dupatta')
        ->assertSee('Choose a variant')
        ->assertSee('Shipping, care, and policy')
        ->assertSee('Premium combos you may love');
});

it('hydrates variant image links on the standard product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->with(['images', 'variants'])->firstOrFail();
    $image = $product->images->firstOrFail();
    $variant = $product->variants->firstOrFail();
    $variant->update(['option_values' => ['color:Ruby']]);
    $product->update([
        'variant_media_links' => [
            'color:Ruby' => [(string) $image->id],
        ],
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('variantMediaLinks', false)
        ->assertSee((string) $image->id, false);
});

it('hydrates variant mockup links on the advanced product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')
        ->with('personalizationMockups')
        ->firstOrFail();
    $mockup = $product->personalizationMockups->firstOrFail();

    $product->variants()->create([
        'name' => 'Framed Nikah',
        'sku' => 'AZR-NIK-FRAMED',
        'option_values' => ['frame_type:Framed'],
        'price' => 2900,
        'stock_quantity' => 0,
        'is_default' => true,
        'position' => 0,
    ]);
    $product->update([
        'variant_media_links' => [
            'frame_type:Framed' => [(string) $mockup->id],
        ],
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('variantMediaLinks', false)
        ->assertSee((string) $mockup->id, false);
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
        ->assertSee('Upgrade and save')
        ->assertSee('Nikkah Combo')
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

    $template = PersonalizationTemplate::whereHas('product', fn ($query) => $query->where('slug', 'signature-nikah-nama'))
        ->with('fields')
        ->firstOrFail();
    $field = $template->fields->firstOrFail();
    $field->update([
        'settings' => [
            ...($field->settings ?? []),
            'preset_values' => ['Bismillah', 'Alhamdulillah'],
        ],
    ]);

    $this->get('/products/signature-nikah-nama')
        ->assertOk()
        ->assertSee('Add personalized order')
        ->assertSee('Bismillah')
        ->assertSee('Alhamdulillah')
        ->assertDontSee('Choose preset');
});

it('shows the dedicated light customizable pdp instead of the standard flow', function () {
    $this->seed(CatalogSeeder::class);

    Product::where('slug', 'customized-pen')->firstOrFail()->update([
        'personalization_help_text' => 'Pick a sample or write your own detail.',
        'personalization_fields_blueprint' => [
            [
                'label' => 'Quotation',
                'field_key' => 'quotation',
                'type' => 'textarea',
                'is_required' => true,
                'preset_values' => ['Bismillah', 'Alhamdulillah'],
            ],
        ],
    ]);

    $this->get('/products/customized-pen')
        ->assertOk()
        ->assertSee('Personalize this detail')
        ->assertSee('Quotation')
        ->assertSee('Bismillah')
        ->assertSee('Add personalized item')
        ->assertDontSee('Choose preset');
});

it('adds a light customizable product using a dynamic field value', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'customized-pen')->firstOrFail();
    $product->update([
        'personalization_fields_blueprint' => [
            [
                'label' => 'Quotation',
                'field_key' => 'quotation',
                'type' => 'textarea',
                'is_required' => true,
                'preset_values' => ['Bismillah', 'Alhamdulillah'],
            ],
        ],
    ]);

    $this->from(route('products.show', $product))
        ->post(route('cart.store', $product), [
            'quantity' => 1,
            'personalization' => [
                'quotation' => 'My own dua',
            ],
        ])
        ->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Quotation: My own dua');
});

it('shows the dedicated bundle pdp instead of redirecting back to the shop page', function () {
    $this->seed(CatalogSeeder::class);

    $recentProduct = Product::where('slug', 'customized-pen')->firstOrFail();

    $this->withSession(['recently_viewed_products' => [$recentProduct->id]])
        ->get('/products/nikkah-combo')
        ->assertOk()
        ->assertSeeInOrder(['Nikkah Combo', 'Combo story', 'Everything in this combo', 'How combo pricing works', 'Related combos or individual pieces'])
        ->assertSee('Everything in this combo')
        ->assertSee('Related categories')
        ->assertSee('Last viewed products')
        ->assertSee('Add full combo')
        ->assertSee('variant_groups', false)
        ->assertSee('discounted_line_total', false)
        ->assertSee('/products/signature-nikah-nama/preview-image.png', false);
});

it('shows combo child prices using compare price for display and selling price for extra savings', function () {
    $this->seed(CatalogSeeder::class);

    $bundle = Product::where('slug', 'nikkah-combo')
        ->with('bundleItems.childProduct.variants')
        ->firstOrFail();
    $penItem = $bundle->bundleItems->first(fn ($item) => $item->childProduct->slug === 'customized-pen');
    $silverVariant = $penItem->childProduct->variants->firstWhere('sku', 'AZR-PEN-001-SL');
    $silverVariant->update(['compare_at_price' => 800]);

    $bundle->update([
        'combo_discount_type' => 'percent',
        'combo_discount_value' => 12.5,
    ]);

    $percentSummary = ComboPricing::summary($bundle->fresh(), [$penItem->id => $silverVariant->id]);
    $percentPen = $percentSummary['items']->firstWhere('child_product_id', $penItem->child_product_id);

    expect($percentPen['line_total'])->toBe(800.0)
        ->and($percentPen['standalone_line_total'])->toBe(700.0)
        ->and($percentPen['discounted_line_total'])->toBe(612.5)
        ->and($percentSummary['regular_total'])->toBe(3300.0)
        ->and($percentSummary['standalone_total'])->toBe(3200.0);

    $bundle->update([
        'combo_discount_type' => 'fixed',
        'combo_discount_value' => 330,
    ]);

    $fixedSummary = ComboPricing::summary($bundle->fresh(), [$penItem->id => $silverVariant->id]);
    $fixedPen = $fixedSummary['items']->firstWhere('child_product_id', $penItem->child_product_id);

    expect($fixedPen['line_total'])->toBe(800.0)
        ->and($fixedPen['standalone_line_total'])->toBe(700.0)
        ->and($fixedPen['discounted_line_total'])->toBe(627.81);
});
