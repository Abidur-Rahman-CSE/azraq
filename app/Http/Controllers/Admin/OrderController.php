<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusUpdateRequest;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::withCount('items')->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'events']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(OrderStatusUpdateRequest $request, Order $order)
    {
        $before = [
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'shipping_status' => $order->shipping_status,
        ];

        $statuses = $request->safe()->except(['note']);

        $order->update($statuses);
        $order->items()->update([
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
        ]);

        $order->events()->create([
            'event_type' => 'status_updated',
            'message' => 'Admin updated order statuses.',
            'meta' => [
                'before' => $before,
                'after' => [
                    'payment_status' => $order->payment_status,
                    'fulfillment_status' => $order->fulfillment_status,
                    'shipping_status' => $order->shipping_status,
                ],
                'note' => $request->input('note'),
            ],
        ]);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order statuses updated.');
    }
}
