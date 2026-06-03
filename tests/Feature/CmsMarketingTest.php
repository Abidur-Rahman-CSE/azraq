<?php

use App\Models\BookingRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Page;
use App\Models\Review;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows cms-backed homepage and faq content', function () {
    $this->seed(CatalogSeeder::class);

    $this->get('/')
        ->assertOk()
        ->assertSee('A premium bridal storefront with configurable homepage sections.')
        ->assertSee('Frequently asked questions');

    $this->get(route('faq.index'))
        ->assertOk()
        ->assertSee('How long does a Nikah Nama proof take?')
        ->assertSee('Personalization');
});

it('applies a coupon to the cart summary', function () {
    $this->seed(CatalogSeeder::class);

    $product = \App\Models\Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->post(route('cart.store', $product), [
        'quantity' => 1,
        'variant_id' => $product->variants()->first()->id,
    ]);

    $this->post(route('cart.coupon.store'), [
        'code' => 'AZRAQ10',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Applied coupon')
        ->assertSee('AZRAQ10');
});

it('shows published cms pages', function () {
    $this->seed(CatalogSeeder::class);

    $page = Page::where('slug', 'about')->firstOrFail();

    $this->get(route('pages.show', $page))
        ->assertOk()
        ->assertSee('About Azraq Bridal');

    $policyPage = Page::where('slug', 'privacy-policy')->firstOrFail();

    $this->get(route('pages.show', $policyPage))
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSeeText('Terms & Conditions');
});

it('shows a lightweight account hub with recent customer activity', function () {
    $this->seed(CatalogSeeder::class);

    $order = Order::create([
        'order_number' => 'AZR-ACCT01',
        'customer_name' => 'Account Customer',
        'customer_email' => 'account@example.com',
        'customer_phone' => '01700000077',
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

    $booking = BookingRequest::create([
        'product_id' => \App\Models\Product::where('slug', 'bridal-booking')->firstOrFail()->id,
        'booking_number' => 'BKG-ACCT01',
        'customer_name' => 'Account Customer',
        'customer_email' => 'account@example.com',
        'customer_phone' => '01700000077',
        'preferred_date' => now()->addDays(5)->toDateString(),
        'preferred_time' => 'Evening',
        'location_area' => 'Dhaka',
        'package_details' => 'Bridal booking',
        'notes' => 'Account page test',
        'status' => 'pending',
        'deposit_required' => false,
        'deposit_amount' => null,
        'deposit_status' => 'not_required',
    ]);

    $product = \App\Models\Product::where('slug', 'bridal-dupatta')->firstOrFail();

    $this->withSession([
        'recent_order_ids' => [$order->id],
        'recent_booking_ids' => [$booking->id],
        'wishlist.product_ids' => [$product->id],
    ])->get(route('account.index'))
        ->assertOk()
        ->assertSee('Your Azraq customer hub')
        ->assertSee('AZR-ACCT01')
        ->assertSee('BKG-ACCT01')
        ->assertSee('Bridal Dupatta');
});

it('loads admin cms and marketing modules and moderates reviews', function () {
    signInAdmin($this);

    $this->seed(CatalogSeeder::class);

    $this->get(route('admin.content.homepage-sections.index'))->assertOk()->assertSee('Homepage sections');
    $this->get(route('admin.marketing.coupons.index'))->assertOk()->assertSee('Coupons');
    $this->get(route('admin.reviews.index'))->assertOk()->assertSee('Review moderation');

    $review = Review::firstOrFail();

    $this->put(route('admin.reviews.update', $review), [
        'is_approved' => 0,
    ])->assertRedirect(route('admin.reviews.index'));

    expect($review->fresh()->is_approved)->toBeFalse();
});
