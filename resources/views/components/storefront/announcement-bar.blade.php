@php($announcementText = $siteSettings['announcement_text'] ?? 'Made-to-order bridal details, premium Nikah personalization, and event-ready gifting.')
@php($announcementCtaLabel = $siteSettings['announcement_cta_label'] ?? null)
@php($announcementCtaHref = $siteSettings['announcement_cta_href'] ?? null)
@php($supportPhone = $siteSettings['support_phone'] ?? config('brand.contact.whatsapp'))

<div class="announcement-luxury">
    <div class="container-shell flex flex-col gap-2 px-0 py-2.5 text-center text-xs sm:flex-row sm:items-center sm:justify-between">
        <p class="text-white/75 tracking-[0.04em]">
            <span class="text-[var(--azraq-blue)] text-[0.6rem]">◆</span>
            <span class="ml-1.5">{{ $announcementText }}</span>
            @if ($announcementCtaLabel && $announcementCtaHref)
                <a class="ml-2 text-[var(--azraq-cream)] font-semibold underline decoration-white/20 underline-offset-4" href="{{ $announcementCtaHref }}">{{ $announcementCtaLabel }}</a>
            @endif
        </p>
        <p class="hidden sm:flex items-center gap-1 text-white/55 whitespace-nowrap">
            <span class="text-[var(--azraq-blue)] text-[0.6rem]">◆</span>
            WhatsApp:
            <a class="text-white/80 font-medium hover:text-[var(--azraq-cream)] transition-colors" href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a>
        </p>
    </div>
</div>
