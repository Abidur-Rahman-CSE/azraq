@php($announcementText = $siteSettings['announcement_text'] ?? 'Made-to-order bridal details, premium Nikah personalization, and event-ready gifting.')
@php($announcementCtaLabel = $siteSettings['announcement_cta_label'] ?? null)
@php($announcementCtaHref = $siteSettings['announcement_cta_href'] ?? null)
@php($supportPhone = $siteSettings['support_phone'] ?? config('brand.contact.whatsapp'))

<div class="announcement-strip">
    <div class="container-shell flex flex-col gap-2 px-0 py-3 text-center text-sm sm:flex-row sm:items-center sm:justify-between">
        <p class="text-white/90">
            {{ $announcementText }}
            @if ($announcementCtaLabel && $announcementCtaHref)
                <a class="ml-2 font-medium underline decoration-white/40 underline-offset-4" href="{{ $announcementCtaHref }}">{{ $announcementCtaLabel }}</a>
            @endif
        </p>
        <p class="hidden sm:block text-white/80">
            WhatsApp:
            <a class="font-medium underline decoration-white/40 underline-offset-4" href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a>
        </p>
    </div>
</div>
