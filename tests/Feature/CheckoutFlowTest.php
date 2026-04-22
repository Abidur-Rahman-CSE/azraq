<?php

use App\Models\Order;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use App\Support\NikahRenderPreview;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('shows the checkout page from a populated cart', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'variant_id' => $product->variants()->first()->id,
    ]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Complete your order')
        ->assertSee('Order summary');
});

it('creates an order from the cart and clears the cart session', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $font->id,
        'proof_note' => 'Please review spacing before approval.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
        ],
    ]);

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Amena Rahman',
        'customer_email' => 'amena@example.com',
        'customer_phone' => '01700000000',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 10, House 7',
            'line_2' => 'Dhanmondi',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1209',
            'country' => 'Bangladesh',
        ],
    ]);

    $order = Order::first();

    $response->assertRedirect(route('orders.success', $order));

    expect($order)->not->toBeNull()
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->personalization_status)->toBe('awaiting_proof')
        ->and(data_get($order->items->first()->line_item_meta, 'render_preview.template.name'))->toBe($template->name)
        ->and(data_get($order->items->first()->line_item_meta, 'render_preview.mockup.title'))->toBe('Signature table setting')
        ->and(session('cart.items'))->toBeNull();
});

it('uses the customer selected mockup from the product page through checkout', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();
    $mockup = $product->personalizationMockups()->skip(1)->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $font->id,
        'mockup_id' => $mockup->id,
        'proof_note' => 'Use the selected scene for the proof.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
        ],
    ])->assertRedirect(route('cart.index'));

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Amena Rahman',
        'customer_email' => 'amena@example.com',
        'customer_phone' => '01700000000',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 10, House 7',
            'line_2' => 'Dhanmondi',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1209',
            'country' => 'Bangladesh',
        ],
    ]);

    $order = Order::latest('id')->first();

    $response->assertRedirect(route('orders.success', $order));

    expect(data_get($order->items->first()->line_item_meta, 'mockup_id'))->toBe($mockup->id)
        ->and(data_get($order->items->first()->line_item_meta, 'mockup'))->toBe($mockup->title)
        ->and(data_get($order->items->first()->line_item_meta, 'render_preview.mockup.id'))->toBe($mockup->id)
        ->and(data_get($order->items->first()->line_item_meta, 'render_preview.mockup.title'))->toBe($mockup->title);
});

it('tracks an order by order number and email', function () {
    $this->seed(CatalogSeeder::class);

    $order = Order::create([
        'order_number' => 'AZR-TRACK01',
        'customer_name' => 'Track Customer',
        'customer_email' => 'track@example.com',
        'customer_phone' => '01700000001',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'shipping_status' => 'not_shipped',
        'currency' => 'BDT',
        'subtotal_amount' => 1000,
        'shipping_amount' => 120,
        'discount_amount' => 0,
        'total_amount' => 1120,
        'shipping_address' => ['line_1' => 'X', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
        'billing_address' => ['line_1' => 'X', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
    ]);

    $order->items()->create([
        'product_name' => 'Tracking Item',
        'product_type' => 'standard',
        'quantity' => 1,
        'unit_price' => 1000,
        'subtotal_amount' => 1000,
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'personalization_status' => 'not_applicable',
    ]);

    $this->post(route('orders.track'), [
        'order_number' => 'AZR-TRACK01',
        'customer_email' => 'track@example.com',
    ])
        ->assertOk()
        ->assertSee('Tracking result')
        ->assertSee('AZR-TRACK01');
});

it('allows the customer to approve a generated proof from the tracked order page', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();
    $mockup = $product->personalizationMockups()->firstOrFail();

    $order = Order::create([
        'order_number' => 'AZR-PROOF01',
        'customer_name' => 'Proof Customer',
        'customer_email' => 'proof-customer@example.com',
        'customer_phone' => '01700000005',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'shipping_status' => 'not_shipped',
        'currency' => 'BDT',
        'subtotal_amount' => 2500,
        'shipping_amount' => 120,
        'discount_amount' => 0,
        'total_amount' => 2620,
        'shipping_address' => ['line_1' => 'Road 1', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
        'billing_address' => ['line_1' => 'Road 1', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
    ]);

    $item = $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_type' => $product->type?->value,
        'sku' => $product->sku,
        'quantity' => 1,
        'unit_price' => 2500,
        'subtotal_amount' => 2500,
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'personalization_status' => 'awaiting_proof',
        'line_item_meta' => [
            'font' => $font->name,
            'personalization' => [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
                'ceremony_date' => '12 December 2026',
                'venue' => 'Dhaka',
            ],
            'generated_proofs' => [
                'flat' => [
                    'svg' => [
                        'latest' => ['url' => '/storage/proofs/example-flat.svg'],
                        'history' => [],
                    ],
                ],
            ],
            'render_preview' => NikahRenderPreview::buildForProduct($product, [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
                'ceremony_date' => '12 December 2026',
                'venue' => 'Dhaka',
            ], $font, $template, $mockup),
        ],
    ]);

    $this->post(route('orders.proof.update', [$order, $item]), [
        'customer_email' => 'proof-customer@example.com',
        'decision' => 'approve',
        'note' => 'Looks good to me.',
    ])->assertOk()
        ->assertSee('Your proof response has been recorded.');

    $item->refresh();

    expect($item->personalization_status)->toBe('proof_approved')
        ->and(data_get($item->line_item_meta, 'customer_proof_decision'))->toBe('approve')
        ->and(data_get($item->line_item_meta, 'customer_proof_note'))->toBe('Looks good to me.');
});

it('allows the customer to review and approve proof from a signed link', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();
    $mockup = $product->personalizationMockups()->firstOrFail();

    $order = Order::create([
        'order_number' => 'AZR-SIGNED01',
        'customer_name' => 'Signed Customer',
        'customer_email' => 'signed@example.com',
        'customer_phone' => '01700000006',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'shipping_status' => 'not_shipped',
        'currency' => 'BDT',
        'subtotal_amount' => 2500,
        'shipping_amount' => 120,
        'discount_amount' => 0,
        'total_amount' => 2620,
        'shipping_address' => ['line_1' => 'Road 1', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
        'billing_address' => ['line_1' => 'Road 1', 'city' => 'Dhaka', 'area' => 'Dhaka', 'country' => 'Bangladesh'],
    ]);

    $item = $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_type' => $product->type?->value,
        'sku' => $product->sku,
        'quantity' => 1,
        'unit_price' => 2500,
        'subtotal_amount' => 2500,
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'personalization_status' => 'awaiting_proof',
        'line_item_meta' => [
            'font' => $font->name,
            'personalization' => [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
            ],
            'generated_proofs' => [
                'flat' => [
                    'png' => [
                        'latest' => ['url' => '/storage/proofs/example-flat.png'],
                        'history' => [],
                    ],
                    'svg' => [
                        'latest' => ['url' => '/storage/proofs/example-flat.svg'],
                        'history' => [],
                    ],
                ],
                'mockup' => [
                    'png' => [
                        'latest' => ['url' => '/storage/proofs/example-mockup.png'],
                        'history' => [],
                    ],
                ],
            ],
            'render_preview' => NikahRenderPreview::buildForProduct($product, [
                'bride_name' => 'Amena',
                'groom_name' => 'Hassan',
            ], $font, $template, $mockup),
        ],
    ]);

    $signedUrl = URL::temporarySignedRoute('orders.proof.review', now()->addDay(), [$order, $item]);

    $this->get($signedUrl)
        ->assertOk()
        ->assertSee('Proof review')
        ->assertSee('Rendered proof previews')
        ->assertSee('/storage/proofs/example-flat.png')
        ->assertSee('/storage/proofs/example-mockup.png')
        ->assertSee('Approve proof');

    $this->post($signedUrl, [
        'decision' => 'changes_requested',
        'note' => 'Please reduce spacing.',
    ])->assertOk()
        ->assertSee('Your proof response has been recorded.');

    $item->refresh();

    expect($item->personalization_status)->toBe('changes_requested')
        ->and(data_get($item->line_item_meta, 'customer_proof_decision'))->toBe('changes_requested');
});

it('requires billing address details when billing differs from shipping', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'variant_id' => $product->variants()->first()->id,
    ]);

    $this->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'customer_name' => 'Billing Test',
            'customer_email' => 'billing@example.com',
            'customer_phone' => '01700000111',
            'shipping_method' => 'standard',
            'payment_method' => 'cod',
            'billing_same_as_shipping' => 0,
            'shipping_address' => [
                'line_1' => 'Road 10, House 7',
                'line_2' => 'Dhanmondi',
                'city' => 'Dhaka',
                'area' => 'Dhaka',
                'postal_code' => '1209',
                'country' => 'Bangladesh',
            ],
            'billing_address' => [
                'line_1' => '',
                'city' => '',
                'area' => '',
                'country' => '',
            ],
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors([
            'billing_address.line_1',
            'billing_address.city',
            'billing_address.area',
            'billing_address.country',
        ]);
});

it('blocks checkout when stock has become unavailable after the item was added to cart', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();
    $variant = $product->variants()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'variant_id' => $variant->id,
    ]);

    $variant->update(['stock_quantity' => 0]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Stock Test',
        'customer_email' => 'stock@example.com',
        'customer_phone' => '01700000112',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 10, House 7',
            'line_2' => 'Dhanmondi',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1209',
            'country' => 'Bangladesh',
        ],
    ])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});
