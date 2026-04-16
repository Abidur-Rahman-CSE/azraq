# CMS Section Schema

## Homepage Section Direction
Homepage sections should be reorderable, enable/disable capable, and configurable from admin.

## Recommended Section Types
- `hero`
- `featured_categories`
- `signature_nikah`
- `best_sellers`
- `combo_spotlight`
- `trust_badges`
- `testimonials`
- `personalized_product_promo`
- `booking_cta`
- `faq_preview`
- `social_proof`

## Suggested Base Fields
- `type`
- `is_enabled`
- `sort_order`
- `title`
- `subtitle`
- `content`
- `cta_label`
- `cta_href`
- `media`
- `settings_json`

## Rendering Rule
Section rendering should map section data to reusable Blade components rather than storing presentation-heavy HTML in the database.
