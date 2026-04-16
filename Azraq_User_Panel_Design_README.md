# Azraq Bridal — User Panel Design README

## Purpose
This README defines the **user panel only** for Azraq Bridal. It is written so Codex can build the storefront **page by page** with a consistent design language, reusable components, and clean product-type separation.

This README is intentionally strict about structure because the storefront must feel like a **premium bridal commerce experience**, not a generic ecommerce theme.

---

# 1. Core Direction

## 1.1 Product philosophy
Azraq Bridal is not a generic catalog. The storefront must support **four distinct product experiences**:

1. **Standard products**
   - Example: Bridal Dupatta, Bridal Mirror, Badge, Churi, Biyer Dala.
   - Simple variant selection, price, gallery, add to cart.

2. **Advanced personalized products**
   - Example: Nikah Nama, Premium Nikah Nama, Nikah Booklet with structured personalization.
   - Template-driven customization.
   - Live preview area.
   - Typography selection.
   - Structured proof fields.
   - Related products designed specifically for ceremonial add-ons.

3. **Light customizable products**
   - Example: Customized Pen, selected bridal add-ons.
   - Limited customization only.
   - Small input fields and maybe one font or engraving style.
   - No heavy typography engine like Nikah Nama.

4. **Service / booking products**
   - Example: Bridal / Non-Bridal / Mehndi Booking.
   - No stock-first purchase experience.
   - Date/time/service inquiry flow.
   - Lead-form-first or booking-request flow.

5. **Bundle / combo products**
   - Example: Nikkah Combo, Bridal Gift Combo, Mehendi Combo.
   - Multi-item composition.
   - Price savings display.
   - Included items module.
   - Upgrade and add-on logic.

---

# 2. Brand Foundation

## 2.1 Brand assets
- **Favicon path:** `storage/images/logo/Azraq.svg`
- **Main logo path:** `storage/images/logo/Azraq.svg`
- Use the Azraq icon on the left.
- On the right, create a clean horizontal wordmark:
  - Top line: `AZRAQ`
  - Bottom line: `Bridal Collection`
- The logo lockup should feel premium, airy, and balanced.
- Do not center-stack the full logo in the header.
- Use the icon on the left and text on the right like the provided reference.

## 2.2 Typography
- Primary font family: **Poppins**
- Use strong contrast between heading weight and body clarity.

### Font scale recommendation
- Display hero heading: 56–72px desktop
- Section title: 34–42px desktop
- Card title: 22–28px
- Product title: 34–46px
- Body copy: 16–18px
- Small labels: 11–13px uppercase or semibold micro text

### Typography rules
- Product and section headings should feel editorial, not techy.
- Small labels can use high letter-spacing.
- Avoid condensed typography.
- Avoid hard black. Use deep navy instead.

## 2.3 Centralized color palette
Create all colors as design tokens.

### Primary palette
- `--azraq-burgundy: #780000`
- `--azraq-red: #C1121F`
- `--azraq-cream: #FDF0D5`
- `--azraq-navy: #003049`
- `--azraq-blue: #669BBC`

### Recommended semantic tokens
- `--bg-page: #F8F5EE`
- `--bg-section-soft: #FBF7F0`
- `--bg-card: #FFFDF9`
- `--text-main: #0F2E3C`
- `--text-muted: #5E6B74`
- `--border-soft: rgba(0, 48, 73, 0.12)`
- `--border-strong: rgba(0, 48, 73, 0.24)`
- `--accent-primary: #780000`
- `--accent-primary-hover: #5E0000`
- `--accent-secondary: #003049`
- `--accent-soft: #669BBC`
- `--pill-bg: #FFF5E9`
- `--success-soft: #E8F5EC`
- `--warning-soft: #FFF2E0`
- `--shadow-soft: 0 10px 30px rgba(20, 30, 40, 0.06)`
- `--shadow-medium: 0 18px 50px rgba(20, 30, 40, 0.10)`

## 2.4 Radius, border, spacing
- Page sections: 88–120px vertical spacing desktop
- Card radius: 24px
- Hero / featured large container radius: 32px
- Pills and chip buttons: full rounded or 999px
- Default grid gap: 24px desktop / 16px mobile

---

# 3. Layout System

## 3.1 Max width
- Global content container: `max-w-[1280px]`
- Product pages: `max-w-[1360px]`
- Checkout/account inner views: `max-w-[1180px]`

## 3.2 Global page rhythm
Every page should follow this pattern:
- announcement bar
- primary header
- page-specific hero or spacing block
- core content sections
- trust / FAQ / recommendation / support sections where relevant
- premium footer

## 3.3 Background strategy
- Page background should not be plain white.
- Use warm bridal cream-based surfaces.
- Alternate soft section backgrounds to create gentle editorial rhythm.

## 3.4 Reusable card styles
Create reusable component variants:
- `surface-card`
- `surface-card-soft`
- `surface-card-featured`
- `surface-product`
- `surface-configurator`
- `surface-sidebar`

---

# 4. Global Components

## 4.1 Announcement bar
Top slim bar.

### Content examples
- Free Dhaka delivery over threshold
- Premium personalization notice
- Seasonal bridal message
- Quick CTA link to Nikah Collection
- WhatsApp / support line at right on desktop

### Behavior
- Minimal height
- Sticky optional on scroll
- Secondary importance, not louder than main header

## 4.2 Header

### Left zone
- Logo icon
- Two-line text lockup:
  - `AZRAQ`
  - `Bridal Collection`

### Center zone
Primary nav items:
- Home
- Shop
- Nikah Collection
- Bridal Wear
- Accessories
- Packages / Combos
- Mehendi & Event
- Bookings
- About

### Right zone
- Search trigger
- Wishlist
- Cart
- Account
- Primary CTA optional: `Book a Consultation` or `Need Help?`

### Header behavior
- Sticky on scroll
- Soft blur background when sticky
- Border-bottom after scroll state
- Mobile: logo + hamburger + cart + wishlist only

## 4.3 Footer
Three-column or four-column premium footer.

### Must contain
- Logo / brand intro
- Short premium bridal value statement
- Shop links
- Support links
- Policy links
- Contact / social
- WhatsApp number
- Instagram / Facebook / Pinterest

### Tone
- Deep navy footer background
- Cream or soft warm white text
- Burgundy CTA button if needed

## 4.4 Buttons
Create standardized button families:
- Primary solid burgundy
- Secondary navy outline or soft filled
- Tertiary ghost
- Pill micro-buttons
- Add-to-cart button
- Buy-now button

## 4.5 Product card system
Need multiple product card types:

1. Standard product card
2. Personalized premium product card
3. Booking/service card
4. Combo/package card
5. Collection highlight card

Each card must have unique badge logic so all products do not look identical.

## 4.6 Related products rail
A shared component but with page-specific heading.

### Use cases
- Related products
- Complete the set
- Pair with this Nikah Nama
- You may also like
- Bridal essentials

---

# 5. Storefront Information Architecture

## 5.1 Main categories
Use the existing categories but make the storefront clearer.

### Customized Bridal Wear
- Bridal Dupatta
- Tail-Cut Dupatta

### Nikah Collection
- Nikah Nama
- Premium Nikah Nama
- Nikah Booklet
- Customized Pen

### Bridal Accessories
- Bridal Mirror / Ayna
- Bridal Badge
- Lehenga Tassel Pillow
- Churi / Bangles

### Wedding Decor & Ritual Items
- Biyer Dala

### Mehendi & Event
- Organic Mehendi
- Bridal Booking
- Non-Bridal Booking
- Mehndi Booking

### Packages / Combos
- Nikkah Combo
- Bridal Gift Combo
- Mehendi Combo

## 5.2 Frontend route groups
- `/`
- `/shop`
- `/category/{slug}`
- `/collection/{slug}`
- `/products/{slug}`
- `/combos/{slug}`
- `/bookings/{slug}`
- `/search`
- `/wishlist`
- `/cart`
- `/checkout`
- `/account/*`
- `/track-order`
- `/about`
- `/contact`
- `/faq`

---

# 6. Page Index

This section defines the order Codex should follow.

1. Global design system and layout shell
2. Header, announcement bar, footer
3. Homepage
4. Shop / all products page
5. Category landing page
6. Collection landing page
7. Standard product details page
8. Advanced Nikah Nama product details page
9. Light customizable product details page
10. Combo / package details page
11. Booking / service details page
12. Search results page
13. Wishlist page
14. Cart page
15. Checkout page
16. Track order page
17. My account pages
18. About page
19. Contact page
20. FAQ page
21. Policy pages

---

# 7. Page-by-Page Design Specification

## 7.1 Global shell prompt

### Objective
Create the base storefront shell with tokens, spacing, typography, reusable surfaces, buttons, and layout containers.

### Codex prompt
Build the Azraq Bridal storefront design system for the user panel only. Use Poppins as the main font, define centralized CSS variables for all Azraq colors, spacing, shadows, radii, text colors, surface colors, and button states. Create a reusable layout shell with announcement bar, sticky header, global container widths, page section spacing, card surfaces, button variants, pill badges, form fields, footer, and responsive behavior. The design should feel premium bridal, elegant, calm, editorial, and warm. Avoid generic marketplace styling.

---

## 7.2 Header + announcement bar + footer

### Header design
- Sticky translucent warm header
- Left aligned horizontal logo lockup
- Center primary nav
- Right utilities: search, wishlist, cart, account, optional CTA
- Mobile drawer with nested category groups

### Footer design
- Large premium footer
- Brand statement on left
- Shop / support / connect columns
- Soft divider lines

### Codex prompt
Design the global announcement bar, header, mobile menu, and footer for Azraq Bridal. Use the logo icon from `storage/images/logo/Azraq.svg` and render the wordmark to the right with `AZRAQ` on the first line and `Bridal Collection` on the second line. The header must feel premium and clean, with a centered navigation and right-side utilities. The footer must use a deep navy background with warm text and premium spacing.

---

## 7.3 Homepage

### Goal
The homepage must immediately communicate that Azraq Bridal sells:
- premium bridal products
- personalized Nikah essentials
- curated combos
- booking/service experiences

### Homepage section order
1. Announcement bar
2. Header
3. Hero section
4. Trust/value strip
5. Featured categories
6. Featured Nikah collection block
7. Featured products grid with mixed product-card types
8. Signature Nikah highlight section
9. Combo/package highlight section
10. Bridal wear spotlight
11. Booking/mehendi services section
12. Testimonials or review strip
13. FAQ preview
14. Instagram / visual gallery optional
15. Footer

### Hero section
Two-column on desktop.

#### Left side
- eyebrow label
- premium headline
- supporting copy
- 2 CTA buttons
- soft benefit chips

#### Right side
Not a generic empty card. Use one of these:
- bridal editorial collage
- featured Nikah certificate + bridal accessory + pen stack
- premium store architecture summary cards

### Value strip
Use 4 to 6 trust items:
- handcrafted finishing
- personalized proof support
- delivery across Bangladesh
- premium gift packaging
- WhatsApp support

### Featured categories
Use 4 or 6 large cards with short descriptions and product count.

### Featured products
This must not be generic. Mix product types visually.

#### Example card labels
- Standard Product
- Advanced Personalized
- Light Customizable
- Service / Booking
- Bundle / Combo

### Signature Nikah highlight block
Must be stronger than a normal featured product section.

#### Layout
- left: image or preview mockup
- right: title, price context, features, personalization capabilities, CTA
- include mini steps of how customization works

### Combo spotlight
Show 2–3 curated combo cards with savings badge.

### FAQ preview
Short list with CTA to full FAQ page.

### Codex prompt
Design the Azraq Bridal homepage as a premium bridal storefront. Use editorial spacing, warm cream surfaces, strong section hierarchy, and a clear distinction between standard products, personalized Nikah products, bundles, and bookings. Include a strong hero, value strip, featured categories, mixed featured products, a dedicated signature Nikah highlight block, combo spotlight, service teaser, testimonials or review strip, FAQ preview, and footer. Make the homepage look rich, finished, and conversion-ready.

---

## 7.4 Shop / all products page

### Purpose
Main catalog page with filters and sorting.

### Layout
- breadcrumb
- page title + short intro
- left filter sidebar desktop / filter drawer mobile
- right product toolbar + grid

### Filters
- category
- collection
- price range
- product type
- availability
- personalization type
- bundle eligible
- booking/service

### Toolbar
- sort dropdown
- item count
- grid toggle optional

### Product grid
Need multiple card types but a unified rhythm.

### Codex prompt
Design the all-products page for Azraq Bridal with a premium filterable catalog layout. Include breadcrumb, page intro, left filter system, top sorting row, and a product grid that supports standard products, personalized products, combo products, and booking/service cards without making the page feel visually inconsistent.

---

## 7.5 Category landing page

### Purpose
Each category page should feel curated, not like a copied shop page.

### Top section
- category title
- short emotional/category-specific description
- optional category image or collage

### Middle
- subcategory shortcuts if available
- featured products in category
- collection highlights relevant to that category

### Example differences
- Nikah Collection page feels premium and ceremonial
- Bridal Accessories page feels giftable and detailed
- Mehendi & Event page feels service-led and festive

### Codex prompt
Design a reusable category landing page for Azraq Bridal that adapts its tone based on category type. The page must support a tailored category hero, optional subcategory navigation, a featured product strip, a filtered product grid, and relevant content blocks so each category feels curated instead of generic.

---

## 7.6 Collection landing page

### Purpose
Collection pages are marketing-led curated groupings.

### Must contain
- collection hero
- collection intent statement
- curated product strip
- editorial supporting block
- optional FAQ or gifting notes

### Example collections
- Best Sellers
- Signature Nikah
n- Gift Picks
- Bridal Essentials

### Codex prompt
Design a collection landing page for curated Azraq Bridal experiences such as Signature Nikah, Gift Picks, and Best Sellers. This page should feel more editorial than the shop page, with a strong collection hero, curated product grouping, supporting content, and a premium landing-page rhythm.

---

## 7.7 Standard product details page

### Applies to
- Bridal Dupatta
- Bridal Mirror / Ayna
- Badge
- Bangles
- Biyer Dala
- similar simple inventory-first products

### Layout
Two-column desktop:
- left: gallery
- right: purchase panel

### Left gallery
- main image area
- thumbnail rail below
- zoom/lightbox
- optional image labels

### Right panel
- breadcrumb
- product title
- price and discount if any
- stock status
- short selling copy
- variant selectors if applicable
- quantity selector
- add to cart
- buy now
- wishlist
- delivery note
- share link

### Below fold section order
1. product details / description
2. specifications
3. shipping / care / policy tabs or accordions
4. related products
5. recently viewed optional

### Related products block
This is missing in the current draft and must be included.

#### It should contain
- heading: `You may also like` or `Complete your bridal set`
- 4 product cards
- category-aware suggestions
- mixed upsell logic optional

### Codex prompt
Design the standard product details page for Azraq Bridal. Use a premium two-column layout with gallery on the left and purchase panel on the right. Include all expected ecommerce elements, but keep the page elegant and not too dense. Below the main product section, add a clear related-products section, product details, shipping/care information, and a soft recommendation flow.

---

## 7.8 Advanced Nikah Nama product details page

### Applies to
- Nikah Nama
- Premium Nikah Nama
- selected structured certificate-like ceremonial products

### This page must be treated as a special product architecture.
It cannot look like a normal product page with only a form beside it.

### Desktop layout
Use a **split premium configurator layout**.

#### Left side
A large sticky visual preview stage.

##### Required elements
- framed preview area with soft surface around it
- template preview with correct aspect ratio
- optional thumbnail rail beneath for lifestyle mockups and closeups
- live text preview overlay region or placeholder area
- small caption: live preview / proof layout / sample shown
- optional zoom / full preview button

#### Right side
A structured configurator sidebar, broken into sections.

### Right side section order
1. breadcrumb
2. product category / personalization badge row
3. product title
4. price + made-to-order note
5. short ceremonial description
6. trust chips
7. structured personalization form card
8. font selection section
9. proof notes textarea
10. quantity and add-to-cart actions
11. wishlist / save design
12. delivery and proof timeline note

### Structured personalization form
Use grouped fields.

#### Required fields
- Bride Name
- Groom Name
- Ceremony Date
- Venue
- Optional custom line / city / Islamic date if needed

#### Field behavior
- max length labels under field
- helper text below each input
- inline validation
- no giant generic textarea for everything

### Font selection
This must be visually strong.

#### Design
- use font preview cards
- each option shows sample text in its real style
- selected state is very obvious
- 4 to 6 curated font choices max
- support distinct font role per field set if needed

### Add-on or option blocks
If product supports:
- frame size
- frame type
- print finish
- premium packaging
- matching pen add-on

Then show them as structured pill/radio groups above or between the personalization sections.

### How it works panel
Add a dedicated informational card under the purchase/configurator area.

#### Include steps
1. fill the structured personalization fields
2. choose typography style
3. add designer note if needed
4. submit with cart
5. proof and fulfillment handled after order

### Related products section for Nikah Nama
This is required and should be more intentional than generic related products.

#### Section title ideas
- Pair with your Nikah order
- Complete your ceremonial set
- Related essentials for your Nikkah

#### Suggested products
- Customized Pen
- Nikah Booklet
- Bridal Badge
- Bridal Mirror / Ayna
- Nikkah Combo

#### Design
Use horizontal premium cards or a 4-card grid.
Each card should show:
- image
- product type badge
- short title
- mini descriptor
- price
- CTA

### Content blocks below the main area
1. Description
2. What is included
3. Personalization policy
4. Proof timeline / delivery expectations
5. FAQ specific to Nikah Nama
6. Related essentials
7. Review/testimonial strip if available

### Mobile behavior
- preview first
- title + pricing second
- form after initial product summary
- sticky mobile CTA area optional

### Codex prompt
Design the advanced Nikah Nama product details page as a premium personalized-product configurator, not a generic ecommerce PDP. The desktop layout must use a large visual preview stage on the left and a structured purchase/configuration column on the right. Include template preview, personalization fields, live-preview-ready layout, curated font selection cards, proof notes, quantity and cart actions, delivery/proof guidance, and a dedicated related-products section specifically for ceremonial add-ons like customized pens, booklets, bridal badges, and combos. The page should feel premium, calm, bridal, and conversion-ready.

---

## 7.9 Light customizable product details page

### Applies to
- Customized Pen
- selected small personalization add-ons

### Difference from Nikah Nama
- lighter and simpler
- no full live typography stage
- no heavy structured proof flow

### Layout
Standard product page base, but include:
- simple personalization field
- engraving/text field
- optional single style selector
- concise helper text

### Codex prompt
Design the light customizable product details page for Azraq Bridal. Use the standard product layout but add a simple personalization block for products like customized pens. Keep the experience lightweight and clean, without turning it into a full advanced configurator like the Nikah Nama page.

---

## 7.10 Combo / package details page

### Applies to
- Nikkah Combo
- Bridal Gift Combo
- Mehendi Combo

### Layout
Two-column or stacked premium bundle layout.

### Must include
- combo hero/gallery
- combo title and savings
- included item list
- visual cards for each included item
- optional upgrade/add-on section
- price breakdown optional
- add full combo to cart button

### Below fold
- what’s included
- who this is for
- gifting/occasion notes
- related combos or individual items

### Codex prompt
Design the combo/package details page for Azraq Bridal. The page must clearly show included items, total value, bundle savings, and premium visual grouping. It should feel curated and giftable, with a strong add-full-combo flow and supporting sections for included products and optional upgrades.

---

## 7.11 Booking / service details page

### Applies to
- Bridal Booking
- Non-Bridal Booking
- Mehndi Booking

### Important
Do not treat these like stock products.

### Layout
- hero image or editorial service visual
- service details column
- booking request form or inquiry form
- package or service scope cards

### Form fields
- name
- phone
- event type
- preferred date
- city/location
- note

### Support blocks
- what’s included
- booking steps
- WhatsApp support CTA
- FAQ

### Codex prompt
Design the booking/service details page for Azraq Bridal so it feels service-led, not inventory-led. Include a premium hero, service description, inquiry or booking form, package highlights, booking steps, trust content, and strong WhatsApp support. Avoid normal ecommerce variant behavior.

---

## 7.12 Search results page

### Must contain
- search header with query
- suggestion chips
- filter and sort
- results grid
- empty state design

### Codex prompt
Design the search results page for Azraq Bridal with premium empty states, filter controls, and a product grid that can support mixed result types including standard products, personalized products, combos, and services.

---

## 7.13 Wishlist page

### Must contain
- saved product cards
- move to cart
- remove
- continue shopping CTA
- empty state

### Codex prompt
Design the Azraq Bridal wishlist page with premium product cards, clear actions, and an elegant empty state that encourages browsing curated categories or collections.

---

## 7.14 Cart page

### Layout
Two-column desktop.

#### Left
- cart items list
- personalization summary for advanced products
- lightweight variant summary for simple products
- combo items grouped clearly

#### Right
- order summary card
- coupon input
- shipping note
- checkout CTA

### Important
For Nikah Nama cart items, show structured personalization summary in a polished way.

### Codex prompt
Design the Azraq Bridal cart page with grouped cart-item designs for standard products, personalized Nikah products, combos, and service add-ons if needed. Show clear personalization summaries, quantity controls, remove actions, coupon field, and a strong order summary sidebar.

---

## 7.15 Checkout page

### Goal
Premium but clean checkout with minimum friction.

### Layout
- left: address + shipping + payment
- right: order summary

### Sections
1. contact information
2. delivery address
3. shipping method
4. payment method
5. order note
6. review summary

### Personalized product treatment
Show a compact personalization recap for Nikah Nama orders.

### Codex prompt
Design the Azraq Bridal checkout page with a premium, low-friction structure. Include a clean form layout, strong visual hierarchy, payment and shipping sections, and an elegant order summary that supports standard products, personalized products, and combos.

---

## 7.16 Track order page

### Layout
- centered premium lookup card
- order number and phone/email input
- delivery info help text
- support CTA

### Codex prompt
Design the Azraq Bridal track-order page with a single premium lookup card, helpful delivery guidance, and support actions for WhatsApp or contact help.

---

## 7.17 My account pages

### Pages
- login
- register
- forgot password
- dashboard
- profile
- address book
- order history
- order details
- wishlist
- logout

### Tone
Minimal and elegant, not dashboard-heavy.

### Codex prompt
Design the Azraq Bridal account area with premium authentication screens and a lightweight customer dashboard. Keep the account section clean, warm, and consistent with the storefront rather than looking like a generic admin dashboard.

---

## 7.18 About page

### Must contain
- brand story
- philosophy
- personalization value
- craftsmanship narrative
- CTA to collections

### Codex prompt
Design the Azraq Bridal about page as an editorial brand page with storytelling, craftsmanship positioning, personalization value, and soft conversion CTAs into the main collections.

---

## 7.19 Contact page

### Must contain
- intro copy
- contact cards
- WhatsApp
- social links
- form
- FAQ shortcut

### Codex prompt
Design the Azraq Bridal contact page with elegant contact cards, WhatsApp-first support emphasis, a clean inquiry form, and soft links to FAQ and order tracking.

---

## 7.20 FAQ page

### Must contain
Grouped accordion sections:
- shipping
- personalization
- proof process
- returns
- combos
- bookings

### Codex prompt
Design the Azraq Bridal FAQ page with grouped accordion sections, premium spacing, and category-based navigation so customers can quickly find answers related to shipping, personalization, proof timing, bundles, and bookings.

---

## 7.21 Policy pages

### Pages
- Shipping Policy
- Return Policy
- Privacy Policy
- Terms & Conditions

### Design
Do not use plain dense text alone.

### Include
- narrow readable content width
- anchored sidebar or page outline optional
- highlight callout cards for important notes

### Codex prompt
Design clean policy pages for Azraq Bridal with readable editorial content width, soft section separators, optional sticky page outline, and highlighted important policy notes.

---

# 8. Nikah Nama-Specific UX Rules

These rules are mandatory for advanced personalized Nikah products.

## 8.1 Do not build it like a normal product page
Nikah Nama must have:
- visual identity of a premium ceremonial product
- structured fields instead of generic notes
- typography selection UI
- proof-aware messaging
- curated related products

## 8.2 Related products are mandatory
On Nikah Nama PDP, include at least one strong recommendation section right after the details or before the footer.

### Prioritize
- Customized Pen
- Nikah Booklet
- Bridal Mirror / Ayna
- Bridal Badge
- Nikkah Combo

## 8.3 Live preview readiness
Even if full live preview logic comes later, the design must be ready for:
- preview image stage
- overlay text placement region
- typography swap states
- template-specific thumbnails

## 8.4 Proof language
Microcopy examples:
- Preview shown for guidance only.
- Final proof may receive minor alignment polish.
- Please use the final spelling for certificate names.
- Proof notes may be added for hierarchy or spelling preferences.

---

# 9. Component Inventory for User Panel

Create reusable components for:
- announcement bar
- header
- mobile menu drawer
- footer
- section heading block
- hero split layout
- trust chip row
- product card: standard
- product card: premium personalized
- product card: light customizable
- product card: service
- product card: combo
- category card
- collection card
- gallery viewer
- product purchase panel
- variant pill group
- personalization field group
- font selection grid
- summary sidebar card
- accordion
- FAQ item
- empty state
- breadcrumb
- price block
- delivery note card
- related products rail
- testimonial card
- filter drawer
- order summary box

---

# 10. Responsive Rules

## Desktop
- generous spacing
- strong two-column sections
- sticky preview for advanced PDPs

## Tablet
- preserve hierarchy
- reduce columns before stacking

## Mobile
- stack clearly
- keep CTA visible
- reduce visual clutter
- on advanced Nikah page, show preview first, then title/price, then key form sections

---

# 11. Quality Bar

The storefront must feel:
- premium
- bridal
- warm
- curated
- readable
- scalable
- reusable

It must not feel:
- generic ecommerce
- harsh or corporate
- empty or under-designed
- fully identical across all product types

---

# 12. Recommended Codex Execution Order

## Phase A
- tokens
- global shell
- header
- footer
- buttons
- cards

## Phase B
- homepage
- shop page
- category page
- collection page

## Phase C
- standard product page
- advanced Nikah product page
- light customizable product page
- combo page
- booking page

## Phase D
- wishlist
- search
- cart
- checkout
- track order

## Phase E
- account pages
- about
- contact
- FAQ
- policies

---

# 13. Final Master Prompt for Codex

Create the full Azraq Bridal user-panel storefront as a premium bridal ecommerce experience with centralized design tokens, Poppins typography, Azraq brand colors, reusable page sections, and product-type-specific layouts. Use `storage/images/logo/Azraq.svg` for both the favicon and the main logo asset. In the header, render the icon on the left and a two-line text lockup on the right with `AZRAQ` above and `Bridal Collection` below. Build the storefront page by page with separate experiences for standard products, advanced personalized Nikah products, light customizable products, bundle/combo products, and booking/service products. The advanced Nikah Nama product page must include a large visual preview area, structured personalization fields, font selection cards, proof notes, premium purchase actions, and a dedicated related-products section for ceremonial add-ons. All pages must feel warm, premium, curated, and conversion-ready rather than generic.

