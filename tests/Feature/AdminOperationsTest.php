<?php

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\PersonalizationTemplate;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates inventory movements when stock-managed products are ordered', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-dupatta')->firstOrFail();
    $variant = $product->variants()->firstOrFail();
    $before = $variant->stock_quantity;

    $this->post(route('cart.store', $product), [
        'quantity' => 2,
        'variant_id' => $variant->id,
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ops Customer',
        'customer_email' => 'ops@example.com',
        'customer_phone' => '01711111111',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 1',
            'line_2' => '',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1207',
            'country' => 'Bangladesh',
        ],
    ]);

    $variant->refresh();

    expect($variant->stock_quantity)->toBe($before - 2)
        ->and(InventoryMovement::count())->toBe(1)
        ->and(InventoryMovement::first()->type)->toBe('sale');
});

it('shows the admin inventory overview', function () {
    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.inventory.index'))
        ->assertOk()
        ->assertSee('Inventory overview')
        ->assertSee('Manual adjustment');
});

it('updates order statuses and adds an order timeline event', function () {
    $this->seed(CatalogSeeder::class);

    $order = Order::create([
        'order_number' => 'AZR-OPS001',
        'customer_name' => 'Ops Admin',
        'customer_email' => 'admin@example.com',
        'customer_phone' => '01722222222',
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
        'product_name' => 'Ops Test Item',
        'product_type' => 'standard',
        'quantity' => 1,
        'unit_price' => 1000,
        'subtotal_amount' => 1000,
        'payment_status' => 'unpaid',
        'fulfillment_status' => 'pending',
        'personalization_status' => 'not_applicable',
    ]);

    $this->put(route('admin.orders.update', $order), [
        'payment_status' => 'paid',
        'fulfillment_status' => 'processing',
        'shipping_status' => 'packed',
        'note' => 'Packed and payment confirmed.',
    ])->assertRedirect(route('admin.orders.show', $order));

    $order->refresh();

    expect($order->payment_status)->toBe('paid')
        ->and($order->fulfillment_status)->toBe('processing')
        ->and($order->shipping_status)->toBe('packed')
        ->and($order->events()->count())->toBe(1);
});

it('shows and updates the order personalization review page', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::with('mockups')->whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $font->id,
        'proof_note' => 'Please keep the bride name centered.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Proof Customer',
        'customer_email' => 'proof@example.com',
        'customer_phone' => '01711111111',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 1',
            'line_2' => '',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1207',
            'country' => 'Bangladesh',
        ],
    ]);

    $order = Order::with('items')->latest()->firstOrFail();
    $item = $order->items->firstWhere('product_id', $product->id);

    $this->get(route('admin.orders.personalization.show', [$order, $item]))
        ->assertOk()
        ->assertSee('Order personalization review')
        ->assertSee('Customer personalization inputs')
        ->assertSee('Please keep the bride name centered.');

    $mockup = $template->mockups()->firstOrFail();

    $this->put(route('admin.orders.personalization.update', [$order, $item]), [
        'personalization_status' => 'proof_approved',
        'template_id' => $template->id,
        'mockup_id' => $mockup->id,
        'internal_note' => 'Approved for production.',
        'review_note' => 'Proof approved after spacing check.',
    ])->assertRedirect(route('admin.orders.personalization.show', [$order, $item]));

    $item->refresh();

    expect($item->personalization_status)->toBe('proof_approved')
        ->and($item->line_item_meta['review_template_id'])->toBe($template->id)
        ->and($item->line_item_meta['review_mockup_id'])->toBe($mockup->id)
        ->and($item->line_item_meta['internal_note'])->toBe('Approved for production.')
        ->and(data_get($item->line_item_meta, 'render_preview.template.name'))->toBe($template->name)
        ->and(data_get($item->line_item_meta, 'render_preview.mockup.title'))->toBe($mockup->title)
        ->and($order->events()->count())->toBeGreaterThan(0);
});

it('allows admins to reissue signed customer proof links with different expiry windows', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::with('mockups')->whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $font->id,
        'proof_note' => 'Please share the renewed proof link.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Renewed Link Customer',
        'customer_email' => 'renew@example.com',
        'customer_phone' => '01711111111',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 1',
            'line_2' => '',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1207',
            'country' => 'Bangladesh',
        ],
    ]);

    $order = Order::with('items')->latest()->firstOrFail();
    $item = $order->items->firstWhere('product_id', $product->id);

    $response = $this->get(route('admin.orders.personalization.show', [$order, $item]).'?proof_link_days=14');

    $response
        ->assertOk()
        ->assertSee('Reissue signed customer proof link')
        ->assertSee('Valid for 14 days')
        ->assertSee('Open latest link');
});

it('exports personalization proof previews as svg', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'signature-nikah-nama')->firstOrFail();
    $template = PersonalizationTemplate::with('mockups')->whereBelongsTo($product)->firstOrFail();
    $font = $template->fonts()->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'font_id' => $font->id,
        'proof_note' => 'Export this proof.',
        'personalization' => [
            'bride_name' => 'Amena',
            'groom_name' => 'Hassan',
            'ceremony_date' => '12 December 2026',
            'venue' => 'Dhaka',
        ],
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'SVG Customer',
        'customer_email' => 'svg@example.com',
        'customer_phone' => '01711111111',
        'shipping_method' => 'standard',
        'payment_method' => 'cod',
        'billing_same_as_shipping' => 1,
        'shipping_address' => [
            'line_1' => 'Road 1',
            'line_2' => '',
            'city' => 'Dhaka',
            'area' => 'Dhaka',
            'postal_code' => '1207',
            'country' => 'Bangladesh',
        ],
    ]);

    $order = Order::with('items')->latest()->firstOrFail();
    $item = $order->items->firstWhere('product_id', $product->id);

    $this->get(route('admin.orders.personalization.export', [$order, $item, 'flat']))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertSee('<svg', false)
        ->assertSee('Amena');

    $this->get(route('admin.orders.personalization.export', [$order, $item, 'mockup']))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertSee('<svg', false)
        ->assertSee('Signature table setting');

    $this->get(route('admin.orders.personalization.export', [$order, $item, 'flat', 'png']))
        ->assertOk()
        ->assertHeader('content-type', 'image/png');

    $this->get(route('admin.orders.personalization.export', [$order, $item, 'mockup', 'png']))
        ->assertOk()
        ->assertHeader('content-type', 'image/png');

    $this->get(route('admin.orders.personalization.export', [$order, $item, 'flat']))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8');

    $item->refresh();

    expect(data_get($item->line_item_meta, 'generated_proofs.flat.svg.latest.path'))->toContain('proofs/orders/')
        ->and(data_get($item->line_item_meta, 'generated_proofs.flat.png.latest.url'))->toContain('/storage/proofs/orders/')
        ->and(data_get($item->line_item_meta, 'generated_proofs.mockup.png.latest.path'))->toContain('mockup-proof-v1.png')
        ->and(data_get($item->line_item_meta, 'generated_proofs.flat.svg.history.0.version'))->toBe(1)
        ->and(data_get($item->line_item_meta, 'generated_proofs.flat.svg.history.1.version'))->toBe(2);
});
