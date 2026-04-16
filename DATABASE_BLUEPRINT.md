# Database Blueprint

## Goal
Define a normalized database shape for Azraq Bridal that supports separate product types, bundle logic, advanced Nikah Nama personalization, and booking workflows without forcing all items into one model.

## Core Domains
- Identity: `users`, `customer_profiles`, `admins`, `roles`, `permissions`
- Catalog: `categories`, `collections`, `tags`, `products`, `product_variants`, `product_media`
- Specialized products: `bundle_products`, `bundle_items`, `service_product_meta`, `personalization_templates`, `personalization_fields`, `personalization_fonts`
- Commerce: `carts`, `cart_items`, `orders`, `order_items`, `order_item_personalizations`, `payments`, `shipments`
- Operations: `inventory_movements`, `stock_levels`, `booking_requests`, `activity_logs`
- CMS: `homepage_sections`, `pages`, `faqs`, `settings`, `banners`, `testimonials`, `reviews`

## Modeling Rules
- `products.type` should distinguish `standard`, `light_customizable`, `advanced_personalized`, `bundle`, and `service`.
- Bundle inventory should depend on child item availability rather than a fake standalone stock number.
- Service products should store booking metadata separately from stock logic.
- Nikah Nama personalization should live in dedicated template and field tables, with structured order payloads captured at checkout time.

## Next Build Steps
1. Create enums for product types, stock movement types, order statuses, and booking statuses.
2. Draft migrations for catalog and product subtype metadata.
3. Add order and personalization tables after catalog foundations are stable.
