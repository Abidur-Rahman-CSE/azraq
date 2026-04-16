<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PersonalizationTemplateController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop.index');
Route::get('/search', [StorefrontController::class, 'search'])->name('search.index');
Route::get('/categories/{category:slug}', [StorefrontController::class, 'category'])->name('categories.show');
Route::get('/collections/{collection:slug}', [StorefrontController::class, 'collection'])->name('collections.show');
Route::get('/products/{product:slug}', [ProductDetailController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/products/{product}/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/coupon', [CouponController::class, 'store'])->name('cart.coupon.store');
Route::delete('/cart/coupon', [CouponController::class, 'destroy'])->name('cart.coupon.destroy');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/success/{order}', [OrderController::class, 'success'])->name('orders.success');
Route::get('/track-order', [OrderController::class, 'trackForm'])->name('orders.track.form');
Route::post('/track-order', [OrderController::class, 'track'])->name('orders.track');
Route::get('/account', [AccountController::class, 'index'])->name('account.index');
Route::get('/faq', [PageController::class, 'faq'])->name('faq.index');
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/products/{product}/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/products/{product}/wishlist', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
Route::post('/products/{product}/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/success/{booking}', [BookingController::class, 'success'])->name('bookings.success');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', function () {
        return view('admin.dashboard', [
            'stats' => [
                [
                    'label' => 'Products',
                    'value' => Product::count(),
                    'description' => 'All catalog items across product subtypes.',
                ],
                [
                    'label' => 'Categories',
                    'value' => Category::count(),
                    'description' => 'Primary taxonomy nodes and subcategories.',
                ],
                [
                    'label' => 'Collections',
                    'value' => Collection::count(),
                    'description' => 'Reusable merchandising collections.',
                ],
                [
                    'label' => 'Tags',
                    'value' => Tag::count(),
                    'description' => 'Filter-friendly merchandising tags.',
                ],
            ],
        ]);
    })->name('dashboard');

    Route::prefix('catalog')->name('catalog.')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('collections', CollectionController::class)->except('show');
        Route::resource('tags', TagController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
    });

    Route::prefix('personalization')->name('personalization.')->group(function (): void {
        Route::resource('templates', PersonalizationTemplateController::class)->except('show', 'destroy');
    });

    Route::prefix('content')->name('content.')->group(function (): void {
        Route::get('/homepage-sections', [HomepageSectionController::class, 'index'])->name('homepage-sections.index');
        Route::get('/homepage-sections/{homepageSection}/edit', [HomepageSectionController::class, 'edit'])->name('homepage-sections.edit');
        Route::put('/homepage-sections/{homepageSection}', [HomepageSectionController::class, 'update'])->name('homepage-sections.update');
        Route::resource('faqs', AdminFaqController::class)->except('show', 'destroy');
        Route::resource('pages', AdminPageController::class)->except('show', 'destroy');
    });

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjustments', [InventoryController::class, 'store'])->name('inventory.adjustments.store');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');

    Route::prefix('marketing')->name('marketing.')->group(function (): void {
        Route::resource('coupons', AdminCouponController::class)->except('show', 'destroy');
    });

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
