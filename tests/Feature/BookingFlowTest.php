<?php

use App\Models\BookingRequest;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a dedicated booking detail page for service products', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-booking')->firstOrFail();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSeeInOrder(['Service / Booking', 'Quick facts', 'Request your date', 'Service details', 'Booking flow', 'Related services and products'])
        ->assertSee('What this booking includes')
        ->assertSee('Related categories')
        ->assertSee('Submit booking request');
});

it('creates a booking request and stores it in the recent booking session list', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'bridal-booking')->firstOrFail();

    $response = $this->post(route('bookings.store', $product), [
        'customer_name' => 'Sadia Rahman',
        'customer_email' => 'sadia@example.com',
        'customer_phone' => '01700000099',
        'preferred_date' => now()->addDays(5)->toDateString(),
        'preferred_time' => 'Afternoon',
        'location_area' => 'Dhanmondi, Dhaka',
        'package_details' => 'Signature bridal package',
        'notes' => 'Please confirm artist availability for an afternoon slot.',
    ]);

    $booking = BookingRequest::first();

    $response->assertRedirect(route('bookings.success', $booking))
        ->assertSessionHas('recent_booking_ids', [$booking->id]);

    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe('pending')
        ->and($booking->deposit_status)->toBe('pending');
});

it('shows the recent booking request history from session', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'non-bridal-booking')->firstOrFail();

    $booking = BookingRequest::create([
        'product_id' => $product->id,
        'booking_number' => 'BKG-RECENT1',
        'customer_name' => 'Nusrat Ahmed',
        'customer_email' => 'nusrat@example.com',
        'customer_phone' => '01700000123',
        'preferred_date' => now()->addDays(10)->toDateString(),
        'preferred_time' => 'Morning',
        'location_area' => 'Gulshan, Dhaka',
        'package_details' => 'Minimal glam',
        'notes' => 'Need an early arrival window.',
        'status' => 'pending',
        'deposit_required' => false,
        'deposit_amount' => null,
        'deposit_status' => 'not_required',
    ]);

    $this->withSession(['recent_booking_ids' => [$booking->id]])
        ->get(route('bookings.index'))
        ->assertOk()
        ->assertSee('Recent service requests on this browser session')
        ->assertSee('BKG-RECENT1')
        ->assertSee('Non-Bridal Booking');
});

it('lets admins review and update booking requests', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::where('slug', 'mehendi-booking')->firstOrFail();

    $booking = BookingRequest::create([
        'product_id' => $product->id,
        'booking_number' => 'BKG-ADMIN01',
        'customer_name' => 'Maliha Noor',
        'customer_email' => 'maliha@example.com',
        'customer_phone' => '01700000456',
        'preferred_date' => now()->addDays(8)->toDateString(),
        'preferred_time' => 'Evening',
        'location_area' => 'Banani, Dhaka',
        'package_details' => 'Bridal mehendi plus family add-on',
        'notes' => 'Initial customer note.',
        'status' => 'pending',
        'deposit_required' => true,
        'deposit_amount' => 3000,
        'deposit_status' => 'pending',
    ]);

    $this->get(route('admin.bookings.index'))
        ->assertOk()
        ->assertSee('Booking requests')
        ->assertSee('BKG-ADMIN01');

    $this->get(route('admin.bookings.show', $booking))
        ->assertOk()
        ->assertSee('Manage Booking BKG-ADMIN01')
        ->assertSee('Initial customer note.');

    $this->put(route('admin.bookings.update', $booking), [
        'status' => 'confirmed',
        'deposit_status' => 'paid',
        'notes' => 'Deposit received and slot confirmed.',
    ])->assertRedirect(route('admin.bookings.show', $booking));

    expect($booking->fresh())
        ->status->toBe('confirmed')
        ->deposit_status->toBe('paid')
        ->notes->toContain('Admin note: Deposit received and slot confirmed.');
});
