<?php

use App\Models\PersonalizationTemplate;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the advanced personalized product detail page', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/products/signature-nikah-nama')
        ->assertOk()
        ->assertSee('Signature Nikah Nama')
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
