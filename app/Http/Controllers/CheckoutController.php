<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Support\CartSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $items = CartSession::items($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Add items to your cart before checkout.');
        }

        $coupon = $request->session()->get('cart.coupon_id')
            ? Coupon::find($request->session()->get('cart.coupon_id'))
            : null;

        return view('storefront.checkout.index', [
            'items' => $items,
            'coupon' => $coupon,
            'summary' => CartSession::summary($items, $request->string('shipping_method')->toString() ?: 'standard', $coupon),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $items = CartSession::items($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $coupon = $request->session()->get('cart.coupon_id')
            ? Coupon::find($request->session()->get('cart.coupon_id'))
            : null;
        $summary = CartSession::summary($items, $request->string('shipping_method')->toString(), $coupon);
        $billingSame = $request->boolean('billing_same_as_shipping', true);
        $shippingAddress = $request->input('shipping_address');
        $billingAddress = $billingSame ? $shippingAddress : $request->input('billing_address', []);

        foreach ($items as $item) {
            if (! CartSession::hasSufficientStock($item['product'], $item['variant'], (int) $item['quantity'])) {
                $available = CartSession::availableStock($item['product'], $item['variant']);

                return redirect()->route('cart.index')->withErrors([
                    'cart' => 'Only '.$available.' unit(s) of '.$item['product']->name.' are currently available. Please update your cart before checkout.',
                ]);
            }
        }

        $order = DB::transaction(function () use ($request, $items, $summary, $shippingAddress, $billingAddress, $coupon) {
            $order = Order::create([
                'order_number' => 'AZR-'.strtoupper(Str::random(8)),
                'customer_name' => $request->string('customer_name')->toString(),
                'customer_email' => $request->string('customer_email')->toString(),
                'customer_phone' => $request->string('customer_phone')->toString(),
                'shipping_method' => $request->string('shipping_method')->toString(),
                'payment_method' => $request->string('payment_method')->toString(),
                'payment_status' => $request->string('payment_method')->toString() === 'online' ? 'pending' : 'unpaid',
                'fulfillment_status' => 'pending',
                'shipping_status' => 'not_shipped',
                'currency' => 'BDT',
                'subtotal_amount' => $summary['subtotal'],
                'shipping_amount' => $summary['shipping'],
                'discount_amount' => $summary['discount'],
                'total_amount' => $summary['total'],
                'notes' => $request->input('notes'),
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $variant = $item['variant'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'product_type' => $product->type?->value,
                    'sku' => $variant?->sku ?: $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal_amount' => $item['subtotal'],
                    'payment_status' => $order->payment_status,
                    'fulfillment_status' => 'pending',
                    'personalization_status' => ! empty($item['personalization']) ? 'awaiting_proof' : 'not_applicable',
                    'line_item_meta' => [
                        'variant_name' => $variant?->name,
                        'custom_text' => $item['custom_text'] ?? null,
                        'font' => $item['font']?->name,
                        'proof_note' => $item['proof_note'] ?? null,
                        'personalization' => $item['personalization'] ?? [],
                        'category' => $product->category?->name,
                    ],
                ]);

                if ($product->manage_stock) {
                    $before = $variant?->stock_quantity ?? $product->stock_quantity;
                    $after = max(0, $before - (int) $item['quantity']);

                    if ($variant) {
                        $variant->update(['stock_quantity' => $after]);
                    } else {
                        $product->update(['stock_quantity' => $after]);
                    }

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'order_id' => $order->id,
                        'type' => 'sale',
                        'quantity_change' => -1 * (int) $item['quantity'],
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'notes' => 'Deducted when order '.$order->order_number.' was placed.',
                    ]);
                }
            }

            $order->events()->create([
                'event_type' => 'order_created',
                'message' => 'Order placed from storefront checkout.',
                'meta' => [
                    'payment_method' => $order->payment_method,
                    'shipping_method' => $order->shipping_method,
                    'items_count' => $order->items()->count(),
                    'coupon_code' => $coupon?->code,
                ],
            ]);

            return $order;
        });

        CartSession::clear($request);

        $recentOrders = collect($request->session()->get('recent_order_ids', []))
            ->prepend($order->id)
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $request->session()->put('recent_order_ids', $recentOrders);

        return redirect()->route('orders.success', $order);
    }
}
