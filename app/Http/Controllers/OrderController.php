<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function success(Order $order)
    {
        $order->load('items');

        return view('storefront.orders.success', compact('order'));
    }

    public function index(Request $request)
    {
        $ids = collect($request->session()->get('recent_order_ids', []));

        $orders = Order::withCount('items')
            ->whereIn('id', $ids)
            ->latest()
            ->get()
            ->sortBy(fn ($order) => array_search($order->id, $ids->all()))
            ->values();

        return view('storefront.orders.index', compact('orders'));
    }

    public function trackForm()
    {
        return view('storefront.orders.track');
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
        ]);

        $order = Order::with('items')
            ->where('order_number', $validated['order_number'])
            ->where('customer_email', $validated['customer_email'])
            ->first();

        if (! $order) {
            return back()->withErrors([
                'order_number' => 'No order matched that order number and email combination.',
            ])->withInput();
        }

        return view('storefront.orders.track-result', compact('order'));
    }
}
