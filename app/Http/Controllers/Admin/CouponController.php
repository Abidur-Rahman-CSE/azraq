<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.marketing.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.marketing.coupons.create', ['coupon' => new Coupon(['is_active' => true, 'type' => 'fixed'])]);
    }

    public function store(CouponRequest $request)
    {
        Coupon::create($request->validated() + [
            'code' => strtoupper($request->string('code')->toString()),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.marketing.coupons.index')->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.marketing.coupons.edit', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated() + [
            'code' => strtoupper($request->string('code')->toString()),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.marketing.coupons.index')->with('status', 'Coupon updated.');
    }
}
