<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyCouponRequest;
use App\Models\Coupon;
use App\Support\CartSession;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function store(ApplyCouponRequest $request)
    {
        $coupon = Coupon::where('code', strtoupper($request->string('code')->toString()))
            ->where('is_active', true)
            ->first();

        $items = CartSession::items($request);
        $summary = CartSession::summary($items);

        if (! $coupon) {
            return redirect()->route('cart.index')->with('status', 'Coupon not found or inactive.');
        }

        if ((float) $coupon->minimum_order_amount > (float) $summary['subtotal']) {
            return redirect()->route('cart.index')->with('status', 'Cart total does not meet coupon minimum.');
        }

        $request->session()->put('cart.coupon_id', $coupon->id);

        return redirect()->route('cart.index')->with('status', 'Coupon applied.');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('cart.coupon_id');

        return redirect()->route('cart.index')->with('status', 'Coupon removed.');
    }
}
