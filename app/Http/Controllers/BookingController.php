<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\BookingRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request, Product $product)
    {
        abort_unless($product->type?->value === 'service' && $product->serviceMeta, 404);

        $booking = BookingRequest::create([
            'product_id' => $product->id,
            'booking_number' => 'BKG-'.strtoupper(Str::random(8)),
            'customer_name' => $request->string('customer_name')->toString(),
            'customer_email' => $request->string('customer_email')->toString(),
            'customer_phone' => $request->string('customer_phone')->toString(),
            'preferred_date' => $request->date('preferred_date'),
            'preferred_time' => $request->string('preferred_time')->toString(),
            'location_area' => $request->string('location_area')->toString(),
            'package_details' => $request->input('package_details'),
            'notes' => $request->input('notes'),
            'status' => 'pending',
            'deposit_required' => (bool) $product->serviceMeta->requires_advance_payment,
            'deposit_amount' => $product->serviceMeta->advance_payment_amount,
            'deposit_status' => $product->serviceMeta->requires_advance_payment ? 'pending' : 'not_required',
        ]);

        $recent = collect($request->session()->get('recent_booking_ids', []))
            ->prepend($booking->id)
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $request->session()->put('recent_booking_ids', $recent);

        return redirect()->route('bookings.success', $booking);
    }

    public function success(BookingRequest $booking)
    {
        $booking->load('product');

        return view('storefront.bookings.success', compact('booking'));
    }

    public function index(Request $request)
    {
        $ids = collect($request->session()->get('recent_booking_ids', []));

        $bookings = BookingRequest::with('product')
            ->whereIn('id', $ids)
            ->latest()
            ->get()
            ->sortBy(fn ($booking) => array_search($booking->id, $ids->all()))
            ->values();

        return view('storefront.bookings.index', compact('bookings'));
    }
}
