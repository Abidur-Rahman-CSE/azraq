<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingStatusUpdateRequest;
use App\Models\BookingRequest;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = BookingRequest::with('product')->latest()->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(BookingRequest $booking)
    {
        $booking->load('product');

        return view('admin.bookings.show', compact('booking'));
    }

    public function update(BookingStatusUpdateRequest $request, BookingRequest $booking)
    {
        $booking->update([
            'status' => $request->string('status')->toString(),
            'deposit_status' => $request->string('deposit_status')->toString(),
            'notes' => $request->filled('notes')
                ? trim(($booking->notes ? $booking->notes."\n\n" : '').'Admin note: '.$request->string('notes')->toString())
                : $booking->notes,
        ]);

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'Booking request updated.');
    }
}
