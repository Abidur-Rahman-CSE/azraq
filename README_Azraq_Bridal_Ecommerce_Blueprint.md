# Azraq Bridal E-commerce Blueprint

A phase-by-phase implementation README for building an optimized, customizable, and scalable bridal e-commerce platform with a dedicated **Nikah Nama personalization flow** and simpler product flows for the rest of the catalog.

---

## 1) Project Goal

Build a premium bridal e-commerce platform for **Azraq Bridal** that supports:

- standard physical products,
- lightweight customizable products,
- advanced personalized **Nikah Nama / Premium Nikah Nama** products,
- packages / bundles / combos,
- service/booking type offerings,
- clean admin operations,
- strong performance, maintainability, and future extensibility.

This project must be:

- **brand-centered**,
- **component-driven**,
- **configurable from admin**,
- **fast and SEO-friendly**,
- **optimized for conversion**,
- **easy to expand later**.

---

## 2) Brand Foundation

### Brand Name
Azraq Bridal

### Primary Typography
- **Poppins** as the default UI and brand font.

### Color Palette
Use centralized semantic tokens instead of scattering raw hex values through the codebase.

#### Raw Brand Colors
- `#780000`
- `#C1121F`
- `#FDF0D5`
- `#003049`
- `#669BBC`

### Recommended Semantic Mapping
Do not use raw palette values directly in components unless absolutely needed.

```text
--color-primary-900: #780000;
--color-primary-700: #C1121F;
--color-surface-cream: #FDF0D5;
--color-secondary-900: #003049;
--color-secondary-500: #669BBC;

--color-text-main: #1f1f1f;
--color-text-soft: #5f5f5f;
--color-border-soft: #e7dcc7;
--color-success: #1f8f5f;
--color-warning: #c57a00;
--color-danger: #b42318;
```

### Recommended Usage Rules
- `#780000` → premium primary actions, emphasis, luxury highlights.
- `#C1121F` → sale accents, badges, urgency, active states where needed.
- `#FDF0D5` → page backgrounds, soft cards, warm section surfaces.
- `#003049` → deep contrast text, footer, overlays, premium dark sections.
- `#669BBC` → secondary CTAs, links, informational accents.

### Typography System
Use **Poppins** across the system for consistency. Build a consistent scale.

```text
Display XL: 56/64/700
Display L: 48/56/700
Heading 1: 40/48/700
Heading 2: 32/40/700
Heading 3: 28/36/600
Heading 4: 24/32/600
Title: 20/28/600
Body L: 18/30/400
Body: 16/28/400
Body S: 14/22/400
Label: 13/18/500
Caption: 12/16/500
```

### Important Note on Typography
Even though inspiration sites may use a serif heading style, **default implementation should stay faithful to Azraq Bridal’s Poppins-led identity**. If needed later, create an optional “editorial display font” feature flag, but do not make it the default.

---

## 3) Product Architecture (Very Important)

Do **not** model all products the same way.

The catalog should support **five product types**:

### A. Standard Product
Examples:
- Bridal Dupatta
- Tail-Cut Dupatta
- Bridal Mirror / Ayna
- Bridal Badge
- Lehenga Tassel Pillow
- Churi / Bangles
- Biyer Dala
- Organic Mehendi

Features:
- images,
- variants,
- inventory,
- price,
- related products,
- reviews,
- shipping,
- SEO.

### B. Light Customizable Product
Examples:
- Customized Pen
- possibly selected badge/gift items

Features:
- one or two custom text fields,
- optional font or color selection,
- simple admin-configured rules,
- no heavy dynamic certificate preview required.

### C. Advanced Personalized Template Product
Examples:
- Nikah Nama
- Premium Nikah Nama
- selected premium framed certificate products

Features:
- template-based personalization,
- advanced text zones,
- font selection,
- safe character limits,
- live preview,
- proof data,
- final render/export workflow.

### D. Bundle / Combo Product
Examples:
- Nikkah Combo
- Bridal Gift Combo
- Mehendi Combo

Features:
- multiple included SKUs,
- combo pricing,
- stock dependency,
- package presentation,
- upsell logic.

### E. Service / Booking Product
Examples:
- Bridal / Non-Bridal / Mehendi Booking

Features:
- date/time request,
- area/location,
- package details,
- contact workflow,
- optional advance payment,
- booking request status,
- not treated exactly like inventory-based products.

---

## 4) Refined Category Architecture

Use your existing categories, but normalize them to support UI, SEO, and filtering better.

## Primary Catalog

### 1. Customized Bridal Wear
- Bridal Dupatta
- Tail-Cut Dupatta

### 2. Nikah Collection
- Nikah Nama
- Premium Nikah Nama
- Nikah Booklet
- Customized Pen

### 3. Bridal Accessories
- Bridal Mirror / Ayna
- Bridal Badge
- Lehenga Tassel Pillow
- Churi / Bangles

### 4. Wedding Decor & Ritual Items
- Biyer Dala

### 5. Mehendi & Event
- Organic Mehendi
- Bridal Booking
- Non-Bridal Booking
- Mehendi Booking

### 6. Packages / Combos
- Nikkah Combo
- Bridal Gift Combo
- Mehendi Combo

## Recommended Additional Layers
Besides category and subcategory, also support:

- **Collections**: Eid Edit, Bridal Essentials, Best Sellers, Signature Nikah, Gift Picks
- **Occasions**: Nikkah, Mehendi, Holud, Wedding, Anniversary
- **Tags**: premium, handmade, customizable, giftable, framed, bestseller

### Important Data Modeling Rule
- **Booking items** should be a separate product subtype, not just a normal stock product.
- **Nikah Nama** should be a separate advanced personalization subtype.
- **Combos** should be separate bundle entities, not fake single products.

---

## 5) Recommended Tech Direction

Assuming a Laravel-centric build:

- **Laravel 12**
- **Blade + Alpine.js** for fast, maintainable storefront/admin UI
- **Tailwind CSS** for tokenized design system
- **MySQL**
- **Laravel Queues** for emails/render jobs
- **Spatie Media Library** or equivalent for assets
- **Laravel Scout / Meilisearch (optional later)** for improved search

### Why this direction
- fast build speed,
- good SEO,
- low front-end complexity,
- easier admin/storefront sharing,
- maintainable for growing e-commerce features.

---

## 6) Global Project Standards

## 6.1 Design System Rules
Create a centralized system for:

- colors,
- spacing,
- radius,
- shadows,
- typography,
- container widths,
- breakpoints,
- buttons,
- forms,
- cards,
- badges,
- modal patterns,
- empty states,
- alerts,
- pagination,
- tables.

## 6.2 No Hardcoding Rule
Anything likely to change should be configurable:

- banners,
- section titles,
- homepage ordering,
- featured categories,
- featured products,
- delivery messages,
- announcement bars,
- social links,
- trust badges,
- CTA labels,
- support phone/WhatsApp,
- policy page content.

## 6.3 Performance Rules
- use responsive images,
- lazy-load below-the-fold media,
- use image size variants,
- avoid unnecessary JS libraries,
- SSR-friendly Blade pages,
- cache menu/category/homepage queries,
- queue heavy personalization renders,
- minimize CLS and LCP regressions.

## 6.4 Reusability Rules
Every storefront page must be assembled from reusable components instead of one-off templates.

---

## 7) Centralized Design Tokens Structure

Recommended files:

```text
resources/css/tokens.css
resources/css/components.css
resources/views/components/
config/brand.php
```

### Example Token Groups

#### Colors
- primary
- secondary
- cream surface
- dark surface
- border
- muted text
- success/warning/danger

#### Spacing
```text
4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96
```

#### Radius
```text
sm: 8
md: 12
lg: 16
xl: 20
2xl: 24
pill: 999px
```

#### Shadow Tokens
- card-soft
- card-hover
- modal
- focus-ring

#### Layout Tokens
- container max widths,
- section paddings,
- sticky offsets,
- grid gaps,
- breakpoint-specific spacing.

---

## 8) Layout System

### Global Layout Types
Create standardized layout shells:

1. **Storefront Default Layout**
2. **Storefront Narrow Content Layout**
3. **Product Detail Layout**
4. **Checkout Layout**
5. **Account Dashboard Layout**
6. **Admin Dashboard Layout**

### Storefront Layout Rules
- sticky header,
- optional announcement bar,
- consistent container widths,
- section spacing rhythm,
- footer with grouped links,
- mobile-first navigation,
- breadcrumb support.

### Product Page Layout Types
Use multiple PDP templates:

#### PDP Type A — Standard Product
For accessories, wearables, decor items.

#### PDP Type B — Light Custom Product
For products with a small set of personalization fields.

#### PDP Type C — Advanced Personalized Product
For Nikah Nama / Premium Nikah Nama with live preview and text controls.

#### PDP Type D — Bundle / Combo
For bundled offerings.

#### PDP Type E — Service / Booking
For Mehendi/bridal booking workflows.

---

## 9) Shared Component Inventory

Create these as reusable components.

### Navigation Components
- announcement bar
- desktop nav
- mobile nav drawer
- mega menu / category menu
- breadcrumb
- footer columns

### Display Components
- section header
- product card
- combo card
- testimonial card
- review card
- info badge
- trust badge
- promo strip
- empty state
- pagination

### Commerce Components
- price block
- quantity selector
- variant pills
- color swatches
- size selector
- stock badge
- coupon panel
- delivery estimate block
- related product slider
- category chips

### Form Components
- input
- textarea
- select
- radio card
- checkbox card
- upload input
- date input
- validation message
- helper text
- character counter

### Personalization Components
- preview canvas/container
- template image viewer
- font selector card
- text zone field block
- live preview controller
- personalization summary card
- proof note block

### Modal/Interactive Components
- quick view modal
- size guide modal
- image lightbox
- address modal
- confirm dialog
- review modal

### Admin Components
- stat cards
- filter bar
- table toolbar
- tabs
- side sheet
- media picker
- repeatable field group
- audit timeline
- setting card

---

## 10) Storefront Pages

## Phase-ready Core Pages
- Home
- Shop / Catalog
- Category page
- Collection page
- Product detail pages by type
- Cart
- Checkout
- Order success
- Login / Register / Forgot password
- Customer account
- Wishlist
- Track order
- About
- Contact
- FAQ
- Policy pages

## Recommended Additional Pages
- Sale page
- Combo landing page
- Best Sellers
- Personalized Gifts landing page
- Bridal Essentials landing page
- Event Booking page

---

## 11) Admin Panel Modules

## Core Admin
- dashboard
- roles & permissions
- admin users
- activity/audit logs
- settings

## Catalog Admin
- categories
- subcategories
- collections
- tags
- products
- variants
- related products
- upsell / cross-sell
- bundles / combos
- service products

## Personalization Admin
- personalization templates
- text zones
- font library
- character limit rules
- preview images
- final render settings
- proof workflow settings

## Inventory Admin
- stock entries
- stock adjustments
- warehouse/location support
- low stock alerts
- stock movement history

## Order & Sales Admin
- orders
- invoices
- packing slips
- payment status
- shipping status
- order notes
- proof approval state
- manual order entry

## Customer Admin
- customers
- addresses
- order history
- notes
- tags
- segmentation

## Marketing Admin
- coupons
- discount campaigns
- featured products
- banners
- popups
- newsletter subscribers

## Content / CMS Admin
- homepage builder blocks
- FAQ
- about page
- contact page
- policy pages
- trust badges
- testimonials
- blogs/articles (optional)

## Operations Admin
- shipping rules
- delivery zones
- courier/tracking
- payment gateways
- return/refund/cancel requests
- support tickets/contact requests

## SEO/Admin Utilities
- product/category/page SEO
- redirects
- sitemap helpers
- structured data toggles

---

## 12) Customer Account Modules

- profile management
- saved addresses
- order history
- order details
- invoice download
- order tracking
- wishlist
- return/cancel request
- booking request history
- personalization order summary
- notification preferences

---

## 13) Related Products and Recommendation Strategy

Each PDP should support:

- related products,
- same category products,
- same collection products,
- recently viewed,
- you may also like,
- combo upsells,
- complementary items.

### Example Logic
- Nikah Nama → premium frame, booklet, customized pen, combo products
- Dupatta → accessories, matching decor, gift combo
- Biyer Dala → ritual items, mehendi combo
- Booking page → package add-ons, organic mehendi, bridal accessories

---

## 14) Inventory Model Strategy

Inventory should support:

- product-level stock,
- variant-level stock,
- bundle stock rules,
- reserved stock,
- available stock,
- return stock,
- damaged stock,
- low-stock thresholds.

### Important Notes
- **Service products** do not reduce stock like physical products.
- **Bundle stock** must depend on child item stock.
- **Nikah Nama** may be made-to-order, so inventory may use “made to order / lead time” instead of classic stock deduction in some cases.

---

## 15) Specialized Nikah Nama Features

Only the Nikah Nama-type products need the advanced personalization layer.

### Required Features
- live template preview,
- configurable text fields,
- field-level character limits,
- font selection,
- optional preview-safe auto scaling,
- personalization instructions,
- final proof data,
- add-to-cart with structured personalization payload.

### Not Required for Most Other Products
Do **not** force this level of complexity on all products.

Most products should only need:
- standard product details,
- optional basic customization,
- variants,
- gallery,
- related products,
- reviews,
- cart/checkout support.

### Nikah Nama Personalization Data Model
Each template product should support:

- base template image,
- lifestyle gallery images,
- text zones,
- font options,
- alignment,
- color,
- max length,
- min/max font size,
- line-height,
- letter-spacing,
- admin notes,
- preview rules,
- final render rules.

---

## 16) Homepage Block Strategy

Homepage should be modular and CMS-controlled.

### Recommended Blocks
- hero banner
- featured categories
- signature Nikah section
- best sellers
- combo/packages spotlight
- bridal accessories strip
- trust badges
- testimonials/reviews
- personalized product promo
- event booking CTA
- FAQ preview
- Instagram/social proof block

Each block should be:
- reorderable,
- enable/disable capable,
- configurable from admin.

---

## 17) Optimization Strategy

## Frontend Optimization
- responsive image pipeline,
- lazy loading,
- lightweight Alpine interactivity,
- component caching,
- defer non-critical scripts,
- optimized font loading,
- avoid JS-heavy builder logic.

## Backend Optimization
- eager loading,
- query scopes,
- cached menus/settings,
- queued emails,
- queued personalization proof render,
- media conversion handling.

## SEO Optimization
- semantic URLs,
- product schema,
- breadcrumb schema,
- canonical tags,
- meta title/description per page,
- alt text support,
- fast category pages.

---

## 18) Suggested Database-Level Modules

At minimum, the system should support entities like:

- users
- admins / roles / permissions
- customers
- categories
- collections
- tags
- products
- product_variants
- product_images
- bundle_products
- bundle_items
- service_products or product_service_meta
- product_personalization_templates
- personalization_fields
- personalization_fonts
- inventory_movements
- orders
- order_items
- order_item_personalizations
- payments
- shipments
- coupons
- reviews
- wishlists
- homepage_sections
- pages
- faqs
- settings
- activity_logs

---

## 19) Recommended Phase-by-Phase Delivery Plan

# Phase 0 — Project Foundation
### Goal
Set up architecture, design tokens, layout shells, coding standards, and reusable base.

### Deliverables
- Laravel project setup
- Tailwind theme + CSS tokens
- base layouts
- header/footer shell
- admin shell
- settings/config foundation
- media conventions
- component folder structure

### Codex Prompt
```text
Act as a senior Laravel 12 + Blade + Tailwind architect. Create the project foundation for Azraq Bridal, a premium bridal e-commerce platform. Use Blade, Alpine.js, Tailwind, and MySQL. Build a centralized design system using Poppins and the brand palette #780000, #C1121F, #FDF0D5, #003049, #669BBC. Create reusable layout shells for storefront, product detail, checkout, account, and admin. Add centralized tokens for colors, spacing, radius, shadows, typography, and container widths. Do not hardcode brand values inside scattered components. Prepare the project structure for future modules like standard products, advanced Nikah Nama personalization, bundles, and service bookings.
```

# Phase 1 — Catalog, Categories, Collections, Product Types
### Goal
Model the catalog correctly with multiple product subtypes.

### Deliverables
- category/subcategory CRUD
- collection CRUD
- tag support
- product CRUD
- product type system
- product variants
- related products
- combo/bundle base model
- service product base model

### Codex Prompt
```text
Build the catalog module for Azraq Bridal. Support multiple product types: standard product, light customizable product, advanced personalized template product, bundle/combo product, and service/booking product. Create admin CRUD for categories, subcategories, collections, tags, and products. Add product variants, gallery images, pricing, stock rules, SEO fields, and related product assignment. Bundles must support child items and combo pricing. Service products must support booking-related metadata instead of normal inventory behavior. Keep the system extensible and cleanly separated by product type.
```

# Phase 2 — Storefront Core Experience
### Goal
Build public-facing browsing and conversion-ready product discovery.

### Deliverables
- homepage blocks
- category pages
- collection pages
- shop filters
- search
- product cards
- related products sections
- wishlist base

### Codex Prompt
```text
Create the storefront browsing experience for Azraq Bridal. Build a CMS-driven homepage with configurable sections such as hero, featured categories, signature Nikah section, best sellers, combo spotlight, trust badges, testimonials, and event booking CTA. Build category pages, collection pages, and a shop page with filtering, sorting, responsive product cards, badges, and pagination or load more. Add related products, same-category suggestions, and recently viewed support. Keep the UI premium, warm, elegant, and consistent with the Azraq Bridal token system.
```

# Phase 3 — Standard PDP, Cart, Wishlist
### Goal
Launch all non-advanced product flows first.

### Deliverables
- PDP Type A and Type B
- gallery
- price block
- variant selector
- quantity box
- cart
- wishlist
- reviews base
- shipping estimate block

### Codex Prompt
```text
Build reusable product detail page templates for Azraq Bridal. Create PDP Type A for standard products and PDP Type B for light customizable products. Include gallery, thumbnails, price block, sale pricing, stock badge, variant selectors, quantity selector, add to cart, wishlist, reviews, FAQ area, delivery estimate, related products, and category suggestions. Light customizable products should support small custom fields without the heavy Nikah Nama personalization builder. Keep the templates modular and admin-driven where possible.
```

# Phase 4 — Advanced Nikah Nama Personalization
### Goal
Build the specialized personalized product engine only for Nikah Nama-type products.

### Deliverables
- advanced PDP Type C
- live preview
- text field zones
- font selector
- personalization payload
- admin personalization template manager
- preview rules

### Codex Prompt
```text
Build the advanced personalized product module for Azraq Bridal specifically for Nikah Nama and Premium Nikah Nama products. Create a dedicated PDP Type C with a left-side live preview and right-side personalization panel. Support admin-defined personalization templates with text zones, font options, character limits, alignment, safe width/height, min/max font size, line-height, letter-spacing, and preview images. Use a lightweight real-time preview approach for the storefront and keep the system template-driven rather than freeform. Save personalization data in structured JSON with order items. Do not apply this advanced builder to all products; it should only activate for the relevant product type.
```

# Phase 5 — Cart, Checkout, Orders, Payments, Shipping
### Goal
Turn the storefront into a complete purchase flow.

### Deliverables
- cart
- checkout
- address management
- shipping method logic
- payment methods
- order creation
- invoice basics
- customer order history
- order tracking

### Codex Prompt
```text
Build the checkout and order management flow for Azraq Bridal. Support guest checkout optionally, customer accounts, addresses, shipping rules, coupon application, order summaries, online payment and cash on delivery, order creation, invoice basics, and order tracking. Order items must support standard products, bundle products, and personalized Nikah Nama payloads. Ensure clean separation of payment status, fulfillment status, and personalization/proof status where relevant.
```

# Phase 6 — Inventory, Sales, Admin Operations
### Goal
Give the admin full operational control.

### Deliverables
- stock movements
- low stock alerts
- inventory report
- order status workflows
- admin notes
- payment status updates
- shipping/tracking
- refund/cancel base

### Codex Prompt
```text
Build the operational admin modules for Azraq Bridal including inventory management, stock movements, low stock alerts, order workflow management, payment status handling, shipping/tracking information, returns/cancellations, and reporting basics. Support separate behavior for physical stock, bundle stock dependency, made-to-order items, and service/booking products. Create clear admin interfaces with tables, filters, status badges, and audit-friendly order timelines.
```

# Phase 7 — CMS, SEO, Reviews, Marketing
### Goal
Make the store manageable and growth-ready.

### Deliverables
- homepage CMS
- FAQ/pages
- review moderation
- coupon engine
- banners/promos
- SEO fields
- redirect support
- announcement settings

### Codex Prompt
```text
Build the content, marketing, and SEO modules for Azraq Bridal. Add CMS-driven homepage sections, editable FAQs, policy pages, about/contact content, review moderation, coupons, sale banners, trust badges, and SEO controls for products, categories, and pages. Add support for announcement bars, featured collections, testimonials, and basic redirect/meta management. Keep everything modular, configurable, and aligned with the centralized design system.
```

# Phase 8 — Booking / Service Workflow
### Goal
Handle bridal and mehendi booking flows cleanly.

### Deliverables
- service PDP Type E
- booking request form
- date/time preferences
- admin booking list
- contact workflow
- optional deposit support

### Codex Prompt
```text
Build the service and booking workflow for Azraq Bridal for Bridal Booking, Non-Bridal Booking, and Mehendi Booking products. Create a dedicated PDP Type E optimized for service conversion rather than physical inventory. Support booking request submission, date/time preferences, area/location fields, package details, optional deposit handling, and admin-side booking status management. Keep this flow separate from standard stock-based product purchase logic.
```

# Phase 9 — Performance Hardening and Launch Readiness
### Goal
Prepare for real-world use.

### Deliverables
- caching pass
- image optimization pass
- QA pass
- accessibility cleanup
- SEO/schema pass
- security cleanup
- launch checklist

### Codex Prompt
```text
Perform a production-readiness pass for Azraq Bridal. Optimize performance, caching, image delivery, accessibility, and SEO. Add structured data where relevant, improve CLS/LCP-sensitive areas, reduce unnecessary JavaScript, verify mobile responsiveness, and audit all major storefront and admin flows. Ensure the codebase remains component-driven, customizable, and easy to maintain.
```

---

## 20) Recommended Admin Menu Structure

```text
Dashboard
Catalog
  Categories
  Collections
  Tags
  Products
  Variants
  Bundles / Combos
  Service Products
Personalization
  Templates
  Fields
  Fonts
  Preview Settings
Inventory
  Stock Overview
  Stock Movements
  Low Stock Alerts
Orders
  All Orders
  Pending
  Processing
  Delivered
  Returns / Cancellations
Customers
Marketing
  Coupons
  Banners
  Featured Sections
Content
  Homepage Sections
  FAQs
  Pages
  Testimonials
Reviews
Operations
  Shipping
  Payments
  Booking Requests
Reports
Settings
Admins & Roles
Activity Logs
```

---

## 21) Folder / Code Organization Suggestion

```text
app/
  Actions/
  Data/
  Enums/
  Http/
  Models/
  Services/
  Support/
  ViewModels/
resources/
  css/
    tokens.css
    components.css
  js/
  views/
    components/
    storefront/
    admin/
config/
  brand.php
  commerce.php
```

### Preferred Separation
- use enums for product types and statuses,
- use service classes for personalization and pricing logic,
- use reusable Blade components,
- keep admin/storefront concerns separated,
- avoid giant controller methods.

---

## 22) What to Avoid

- do not treat every product like Nikah Nama,
- do not hardcode category-specific behavior directly into generic components,
- do not mix booking logic with normal inventory deduction,
- do not scatter colors/fonts in inline classes everywhere,
- do not build the entire storefront as one-off pages,
- do not make the personalized preview system overly heavy at MVP stage,
- do not tightly couple front-end preview rendering with final output rendering.

---

## 23) Final Recommendation

The ideal build strategy is:

- **foundation first**,
- **catalog architecture second**,
- **standard storefront third**,
- **Nikah Nama advanced flow after core commerce is stable**,
- **booking/service flow as a separate specialized track**,
- **CMS + SEO + optimization after primary purchase flow is complete**.

This will keep the project:
- organized,
- scalable,
- faster to launch,
- easier to maintain,
- more customizable later.

---

## 24) Next Recommended Files After This README

After this README, create these implementation docs:

1. `DATABASE_BLUEPRINT.md`
2. `ADMIN_SIDEBAR_AND_ROUTES.md`
3. `DESIGN_TOKENS.md`
4. `PRODUCT_TYPE_RULES.md`
5. `NIKAH_NAMA_PERSONALIZATION_SPEC.md`
6. `CHECKOUT_AND_ORDER_FLOW.md`
7. `CMS_SECTION_SCHEMA.md`

