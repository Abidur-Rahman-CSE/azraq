<?php

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
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
