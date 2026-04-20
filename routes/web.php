<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MockupController;
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
use App\Models\BookingRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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
Route::post('/orders/{order}/proof/{item}', [OrderController::class, 'updateProofDecision'])->name('orders.proof.update');
Route::get('/orders/{order}/proof/{item}/review', [OrderController::class, 'proofReview'])
    ->middleware('signed')
    ->name('orders.proof.review');
Route::post('/orders/{order}/proof/{item}/review', [OrderController::class, 'updateProofDecisionSigned'])
    ->middleware('signed')
    ->name('orders.proof.review.update');
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
        $mockupsTableExists = Schema::hasTable('personalization_mockups');
        $mockupMapsTableExists = Schema::hasTable('personalization_mockup_maps');
        $categoriesHaveImages = Schema::hasColumn('categories', 'image_url');

        $activeMockups = $mockupsTableExists
            ? DB::table('personalization_mockups')->where('is_active', true)->count()
            : 0;

        $mockupsMissingMasks = $mockupsTableExists
            ? DB::table('personalization_mockups')->where(function ($query): void {
                $query->whereNull('mask_image_url')->orWhere('mask_image_url', '');
            })->count()
            : 0;

        $mockupsMissingMapping = $mockupsTableExists && $mockupMapsTableExists
            ? DB::table('personalization_mockups')
                ->leftJoin('personalization_mockup_maps', 'personalization_mockups.id', '=', 'personalization_mockup_maps.personalization_mockup_id')
                ->whereNull('personalization_mockup_maps.id')
                ->count()
            : $activeMockups;

        $revenue = (float) (Order::query()->sum('total_amount') ?? 0);
        $ordersCount = Order::query()->count();
        $personalizedOrders = OrderItem::query()
            ->where('product_type', 'advanced_personalized')
            ->count();
        $pendingProofs = OrderItem::query()
            ->where('personalization_status', 'awaiting_proof')
            ->count();
        $lowStockProducts = Product::query()
            ->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();
        $productsWithoutGallery = Product::query()
            ->doesntHave('images')
            ->count();
        $categoriesWithoutImage = $categoriesHaveImages
            ? Category::query()->where(function ($query): void {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })->count()
            : Category::query()->count();
        $activeTemplates = PersonalizationTemplate::query()
            ->where('is_active', true)
            ->count();

        return view('admin.dashboard', [
            'kpis' => [
                [
                    'label' => 'Orders',
                    'value' => number_format($ordersCount),
                    'description' => 'Confirmed and draft orders flowing through the admin panel.',
                ],
                [
                    'label' => 'Revenue',
                    'value' => 'BDT '.number_format($revenue, 2),
                    'description' => 'Gross order value captured across current seeded transactions.',
                ],
                [
                    'label' => 'Personalized Orders',
                    'value' => number_format($personalizedOrders),
                    'description' => 'Advanced personalized line items that need proof-aware handling.',
                ],
                [
                    'label' => 'Low Stock Items',
                    'value' => number_format($lowStockProducts),
                    'description' => 'Stock-managed products at or below their configured threshold.',
                ],
                [
                    'label' => 'Active Templates',
                    'value' => number_format($activeTemplates),
                    'description' => 'Live personalization templates available for Nikah workflows.',
                ],
                [
                    'label' => 'Active Mockups',
                    'value' => number_format($activeMockups),
                    'description' => 'Currently configured renderable lifestyle mockups.',
                ],
            ],
            'alerts' => [
                [
                    'label' => 'Products without gallery images',
                    'value' => $productsWithoutGallery,
                    'description' => 'Catalog items still missing media coverage for premium admin merchandising.',
                    'tone' => $productsWithoutGallery > 0 ? 'warning' : 'success',
                    'href' => route('admin.catalog.products.index'),
                    'action' => 'Review products',
                ],
                [
                    'label' => 'Categories without category images',
                    'value' => $categoriesWithoutImage,
                    'description' => $categoriesHaveImages
                        ? 'These categories need image coverage before homepage and catalog redesign work.'
                        : 'Category image fields are part of the next implementation pass, so every category is pending media setup.',
                    'tone' => $categoriesWithoutImage > 0 ? 'warning' : 'success',
                    'href' => route('admin.catalog.categories.index'),
                    'action' => 'Review categories',
                ],
                [
                    'label' => 'Personalized orders waiting for proof',
                    'value' => $pendingProofs,
                    'description' => 'Line items that are currently blocked on proof review or designer follow-up.',
                    'tone' => $pendingProofs > 0 ? 'danger' : 'success',
                    'href' => route('admin.orders.index'),
                    'action' => 'Open orders',
                ],
                [
                    'label' => 'Mockups missing mapping or masks',
                    'value' => $mockupsMissingMasks + $mockupsMissingMapping,
                    'description' => $mockupsTableExists
                        ? 'Mockup records still need complete perspective maps or production masks.'
                        : 'The mockup module is next; this placeholder tracks unfinished mapping infrastructure.',
                    'tone' => ($mockupsMissingMasks + $mockupsMissingMapping) > 0 ? 'warning' : 'success',
                    'href' => route('admin.mockups.index'),
                    'action' => 'Open mockups',
                ],
            ],
            'recentOrders' => Order::query()->latest()->take(5)->get(),
            'topProducts' => OrderItem::query()
                ->select('product_name', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(subtotal_amount) as revenue'))
                ->groupBy('product_name')
                ->orderByDesc('units_sold')
                ->limit(5)
                ->get(),
            'categoryStats' => [
                [
                    'label' => 'Categories',
                    'value' => Category::query()->count(),
                    'description' => 'Primary taxonomy groups and subcategories.',
                ],
                [
                    'label' => 'Collections',
                    'value' => Collection::query()->count(),
                    'description' => 'Reusable collection landing groups.',
                ],
                [
                    'label' => 'Tags',
                    'value' => Tag::query()->count(),
                    'description' => 'Filter and merchandising tags.',
                ],
                [
                    'label' => 'Booking requests',
                    'value' => BookingRequest::query()->count(),
                    'description' => 'Service-led consultations and booking inquiries.',
                ],
            ],
            'quickActions' => [
                ['label' => 'Add product', 'href' => route('admin.catalog.products.create')],
                ['label' => 'New category', 'href' => route('admin.catalog.categories.create')],
                ['label' => 'Open templates', 'href' => route('admin.personalization.templates.index')],
                ['label' => 'Review orders', 'href' => route('admin.orders.index')],
            ],
        ]);
    })->name('dashboard');

    Route::get('/mockups', [MockupController::class, 'index'])->name('mockups.index');
    Route::get('/mockups/create', [MockupController::class, 'create'])->name('mockups.create');
    Route::post('/mockups', [MockupController::class, 'store'])->name('mockups.store');
    Route::get('/mockups/{mockup}/edit', [MockupController::class, 'edit'])->name('mockups.edit');
    Route::put('/mockups/{mockup}', [MockupController::class, 'update'])->name('mockups.update');
    Route::post('/mockups/{mockup}/duplicate', [MockupController::class, 'duplicate'])->name('mockups.duplicate');

    Route::prefix('catalog')->name('catalog.')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('collections', CollectionController::class)->except('show');
        Route::resource('tags', TagController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
    });

    Route::prefix('personalization')->name('personalization.')->group(function (): void {
        Route::resource('templates', PersonalizationTemplateController::class)->except('show', 'destroy');
        Route::post('templates/{template}/duplicate', [PersonalizationTemplateController::class, 'duplicate'])->name('templates.duplicate');
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
    Route::get('/orders/{order}/personalization/{item}', [AdminOrderController::class, 'showPersonalizationReview'])->name('orders.personalization.show');
    Route::put('/orders/{order}/personalization/{item}', [AdminOrderController::class, 'updatePersonalizationReview'])->name('orders.personalization.update');
    Route::get('/orders/{order}/personalization/{item}/export/{mode}/{format?}', [AdminOrderController::class, 'exportPersonalizationPreview'])
        ->whereIn('mode', ['flat', 'mockup'])
        ->whereIn('format', ['svg', 'png'])
        ->name('orders.personalization.export');
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
