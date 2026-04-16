@php($logoSrc = asset('images/logo/Azraq.svg'))

<footer class="site-footer mt-24 overflow-hidden">
    <div class="container-shell py-16 lg:py-20">
        <div class="footer-grid lg:grid-cols-[1.2fr_0.8fr_0.8fr_0.9fr]">
            <div class="space-y-5">
                <div class="brand-lockup">
                    <span class="brand-mark footer-brand-mark">
                        <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="brand-wordmark">
                        <span class="brand-wordmark-top footer-brand-top">AZRAQ</span>
                        <span class="brand-wordmark-bottom footer-brand-bottom">Bridal Collection</span>
                    </span>
                </div>
                <h2 class="max-w-xl text-3xl font-semibold text-white">Curated bridal keepsakes, ceremonial personalization, and warm support from inquiry to delivery.</h2>
                <p class="max-w-xl text-sm leading-7 text-white/72">
                    Azraq Bridal brings together premium bridal wear, Nikah essentials, giftable accessories, curated combos, and booking-led services in one calm, polished storefront.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('shop.index') }}" class="button-primary">Browse Collections</a>
                    <a href="{{ route('faq.index') }}" class="footer-plain-link">Need Support?</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title">Shop</p>
                <div class="mt-5 space-y-3 text-sm">
                    <a href="{{ url('/categories/nikah-collection') }}" class="footer-link block">Nikah Collection</a>
                    <a href="{{ url('/categories/customized-bridal-wear') }}" class="footer-link block">Bridal Wear</a>
                    <a href="{{ url('/categories/bridal-accessories') }}" class="footer-link block">Accessories</a>
                    <a href="{{ url('/categories/packages-combos') }}" class="footer-link block">Packages / Combos</a>
                    <a href="{{ url('/shop?type=service') }}" class="footer-link block">Bookings</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title">Support</p>
                <div class="mt-5 space-y-3 text-sm">
                    <a href="{{ route('orders.track.form') }}" class="footer-link block">Track Order</a>
                    <a href="{{ route('faq.index') }}" class="footer-link block">FAQ</a>
                    <a href="{{ url('/pages/shipping-policy') }}" class="footer-link block">Shipping Policy</a>
                    <a href="{{ url('/pages/contact') }}" class="footer-link block">Contact</a>
                    <a href="{{ route('wishlist.index') }}" class="footer-link block">Wishlist</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title">Connect</p>
                <div class="mt-5 space-y-3 text-sm text-white/72">
                    <p>{{ config('brand.contact.email') }}</p>
                    <p>{{ config('brand.contact.phone') }}</p>
                    <p>WhatsApp-first support for proofs, bookings, and gifting help.</p>
                    @foreach (config('brand.socials', []) as $social)
                        <a href="{{ $social['href'] }}" class="footer-link block" rel="noopener noreferrer">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-white/10 pt-6 text-sm text-white/55">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p>Azraq Bridal user panel experience. Warm, premium, and product-type-aware by design.</p>
                <p>Powered by handcrafted details, proof support, and calm commerce flow.</p>
            </div>
        </div>
    </div>
</footer>
