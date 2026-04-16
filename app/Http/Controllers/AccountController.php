<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $recentOrderIds = collect($request->session()->get('recent_order_ids', []));
        $recentBookingIds = collect($request->session()->get('recent_booking_ids', []));
        $wishlistIds = collect($request->session()->get('wishlist.product_ids', []));

        $orders = Order::withCount('items')
            ->whereIn('id', $recentOrderIds)
            ->latest()
            ->take(3)
            ->get()
            ->sortBy(fn ($order) => array_search($order->id, $recentOrderIds->all()))
            ->values();

        $bookings = BookingRequest::with('product')
            ->whereIn('id', $recentBookingIds)
            ->latest()
            ->take(3)
            ->get()
            ->sortBy(fn ($booking) => array_search($booking->id, $recentBookingIds->all()))
            ->values();

        $wishlist = Product::with(['category', 'images'])
            ->whereIn('id', $wishlistIds)
            ->latest()
            ->take(3)
            ->get()
            ->sortBy(fn ($product) => array_search($product->id, $wishlistIds->all()))
            ->values();

        return view('storefront.account.index', [
            'orders' => $orders,
            'bookings' => $bookings,
            'wishlist' => $wishlist,
        ]);
    }
}
