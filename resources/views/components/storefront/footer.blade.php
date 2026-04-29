@php($logoSrc = asset('images/logo/Azraq.svg'))

<footer class="section-dark-luxury overflow-hidden" style="--section-dark-bg: var(--bg-dark-luxury);">
    <hr class="divider-gold mx-auto max-w-[1280px]">

    <div class="container-shell py-20 lg:py-24 relative z-10">
        <div class="footer-grid lg:grid-cols-[1.3fr_0.75fr_0.75fr_0.85fr]">
            <div class="space-y-6">
                <div class="brand-lockup">
                    <span class="brand-mark footer-brand-mark" style="border-color: rgba(201,168,76,0.18); background: rgba(201,168,76,0.06);">
                        <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="brand-wordmark">
                        <span class="brand-wordmark-top" style="color: #fff;">AZRAQ</span>
                        <span class="brand-wordmark-bottom" style="color: rgba(102,155,188,0.70); letter-spacing: 0.22em;">Bridal Collection</span>
                    </span>
                </div>

                <p class="text-[0.68rem] uppercase tracking-[0.28em] text-[var(--azraq-blue)] font-semibold">Crafted for moments that last forever</p>

                <p class="max-w-sm text-sm leading-7 text-white/55">
                    Azraq Bridal brings together premium bridal wear, Nikah essentials, giftable accessories, curated combos, and booking-led services — all in one calm, polished storefront.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('shop.index') }}" class="button-luxury">Browse Collections</a>
                    <a href="{{ route('faq.index') }}" class="button-outline-gold">Need Support?</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title" style="color: var(--azraq-blue); letter-spacing: 0.22em;">Shop</p>
                <div class="mt-5 space-y-3 text-sm">
                    <a href="{{ url('/categories/nikah-collection') }}" class="footer-link block">Nikah Collection</a>
                    <a href="{{ url('/categories/customized-bridal-wear') }}" class="footer-link block">Bridal Wear</a>
                    <a href="{{ url('/categories/bridal-accessories') }}" class="footer-link block">Accessories</a>
                    <a href="{{ url('/categories/packages-combos') }}" class="footer-link block">Packages / Combos</a>
                    <a href="{{ url('/shop?type=service') }}" class="footer-link block">Bookings</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title" style="color: var(--azraq-blue); letter-spacing: 0.22em;">Support</p>
                <div class="mt-5 space-y-3 text-sm">
                    <a href="{{ route('orders.track.form') }}" class="footer-link block">Track Order</a>
                    <a href="{{ route('faq.index') }}" class="footer-link block">FAQ</a>
                    <a href="{{ url('/pages/shipping-policy') }}" class="footer-link block">Shipping Policy</a>
                    <a href="{{ url('/pages/contact') }}" class="footer-link block">Contact</a>
                    <a href="{{ route('wishlist.index') }}" class="footer-link block">Wishlist</a>
                </div>
            </div>

            <div>
                <p class="footer-column-title" style="color: var(--azraq-blue); letter-spacing: 0.22em;">Connect</p>
                <div class="mt-5 space-y-3 text-sm text-white/50">
                    <p>{{ config('brand.contact.email') }}</p>
                    <p>{{ config('brand.contact.phone') }}</p>
                    <p class="text-white/40 text-xs leading-6">WhatsApp-first support for proofs, bookings, and gifting help.</p>
                    @foreach (config('brand.socials', []) as $social)
                        <a href="{{ $social['href'] }}" class="footer-link block" rel="noopener noreferrer">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <hr class="divider-gold mt-14 mb-8 opacity-50">

        <div class="flex flex-col gap-2 text-[0.68rem] uppercase tracking-[0.18em] text-white/30 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} Azraq Bridal. All rights reserved.</p>
            <p>Designed with care. Built for ceremony.</p>
        </div>
    </div>
</footer>
