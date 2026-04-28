# Codex Prompt: Improve Azraq Service & Combo Product Details Pages + Admin Customization + Smart Combo Upsell

You are working on the **Azraq Bridal Collection** Laravel ecommerce project.

The current **Signature Nikah Premium** product details page layout is visually the best and should be used as the design reference for the **Service / Booking** and **Bundle / Combo** product details pages.

The goal is to redesign and improve the **service product details page**, **combo product details page**, **admin product editor**, and **marketing upsell logic** while keeping the existing premium Azraq design language.

---

## 1. Design Direction

Follow the visual language of the existing premium product details page:

- Warm ivory / cream background
- Deep teal text and footer
- Deep red CTA buttons
- Rounded cards
- Soft shadows
- Clean spacing
- Premium bridal ecommerce feeling
- Calm, polished, trustworthy layout
- Mobile responsive layout

Do **not** make the service and combo pages look like plain admin/data pages.
They should feel like premium product storytelling pages.

---

## 2. Main Layout Rule

Use a two-column product details layout.

### Left column
Keep the left column sticky on desktop.

The left side should mainly contain:

- Main product/service/combo image
- Gallery carousel or image slider
- Thumbnail gallery
- Image counter if useful
- Small badges such as:
  - Service preview
  - Combo preview
  - Includes 3 pieces
  - Save 12%
  - Made to order

It is okay if the left sticky column only contains image and gallery.
Do not overload the left side with too much text.

### Right column
Use the right side for product-specific actions and information.

For service products, this means booking information and booking form.
For combo products, this means combo pricing, included item summary, quantity, and CTA buttons.

---

## 3. Service / Booking Product Details Page

The current service details page feels right-side-heavy and does not provide enough structured information.
Redesign the service product details page using the premium product details page structure.

### Top area layout

#### Left sticky area
Show:

- Primary service image
- Gallery thumbnails from uploaded gallery images
- Optional small info strip:
  - Duration
  - Location scope
  - Advance amount

#### Right area
Show:

- Service badges
  - Service / Booking
  - Category name
  - Advance required, if enabled
- Service title
- Price or starting price
- Short description
- Quick facts grid:
  - Duration label
  - Location scope
  - Advance amount
  - Confirmation rule
- Compact booking form
- CTA buttons:
  - Submit booking request
  - WhatsApp inquiry, if available

The booking form should not feel visually too heavy.
Use grouped fields and good spacing.

### Booking form fields

Keep or add support for:

- Customer name
- Email
- Phone
- Preferred date
- Preferred time
- Area / location
- Package / scope
- Notes

### Service details sections below top area

Add structured sections below the top area:

1. **What this service includes**
   - Display as small cards or icon cards.
   - Admin should be able to add/edit these items.

2. **Available packages / service scopes**
   - Example: Simple, Bridal, Party, Engagement, Custom.
   - This can be optional.

3. **How booking works**
   - Step 1: Submit request
   - Step 2: Availability check
   - Step 3: Advance payment after confirmation
   - Step 4: Final coordination

4. **Before your appointment**
   - Instructions such as skin prep, guest count, timing, sitting space, etc.

5. **Pricing and extra charges**
   - Extra guests
   - Outside service area
   - Extended hours
   - Complex bridal design

6. **Policy**
   - Advance payment
   - Reschedule
   - Cancellation
   - Late arrival

7. **FAQ**
   - Admin-editable question and answer list.

8. **Gallery / service moments**
   - Show all uploaded gallery images.

9. **Related products**

10. **Related categories**

11. **Last viewed products**

---

## 4. Combo / Bundle Product Details Page

The current combo page has confusion because child products can have variants, and the combo page does not clearly explain which variants are included.
Also, multiple uploaded combo images are not shown properly.

Redesign the combo page so the user clearly understands:

- What is included
- Which variants are included by default
- Whether variants can be changed
- How the price changes
- How much they save

### Top area layout

#### Left sticky area
Show:

- Primary combo image
- Gallery carousel / thumbnails from all uploaded combo gallery images
- Badge examples:
  - Combo preview
  - Includes 3 pieces
  - Save 12%

#### Right area
Show:

- Combo badges:
  - Bundle / Combo
  - Category
  - Save X%
- Combo title
- Short description
- Regular total price
- Discount percentage
- Final combo price
- Savings amount
- Included item summary
- Quantity selector
- CTA buttons:
  - Add full combo
  - Buy it now

### Important combo price logic

Use percentage-based combo pricing.

The combo price should be calculated from the selected child product variants.

Formula:

```text
combo_subtotal = sum(selected_child_variant_price * quantity)
discount_amount = combo_subtotal * discount_percent / 100
combo_final_price = combo_subtotal - discount_amount
```

Show these clearly to the customer:

- Regular total
- Discount percentage
- Savings amount
- Final combo price

Example:

```text
Signature Nikah Premium: BDT 2,500
Customized Pen: BDT 650
Bridal Mirror / Ayna: BDT 1,400
Regular total: BDT 4,550
Combo discount: 12%
Savings: BDT 546
Final combo price: BDT 4,004
```

Rounding rule:

- Add an admin setting for rounding.
- Options:
  - No rounding
  - Round to nearest 10
  - Round to nearest 50
  - Round to nearest 100

### Variant behavior

Use this recommended logic:

- Each child product has a default included variant.
- Admin can allow or disallow variant change for each child product.
- If the user changes to another eligible variant, recalculate the combo subtotal and apply the same discount percentage.
- If a variant is excluded from combo discount, show clear text and add the extra charge separately.

Customer-facing explanation:

```text
This combo includes selected default variants. You may upgrade eligible variants before checkout. Your combo discount is applied to eligible selected items, and any excluded premium upgrade may be charged separately.
```

### Combo details sections below top area

Add structured sections below the top area:

1. **Everything in this combo**
   - Show all included child products.
   - Do not limit to only 2 items.
   - If the combo contains 3 items, show all 3.
   - Each item card should show:
     - Product image
     - Product name
     - Quantity
     - Included/default variant
     - Individual price
     - View item link
     - Variant selector if allowed

2. **Customize your combo**
   - Show variant selection for child products where variant change is allowed.
   - Show price changes live.
   - Show if a selected variant is eligible for combo discount.

3. **How combo pricing works**
   - Explain regular total, combo discount, savings, and final price.

4. **Why choose this combo**
   - Admin-editable story/benefit content.

5. **Who this is for**
   - Example: gifting, ceremony setup, bridal preparation, premium keepsake buyers.

6. **Gallery**
   - Show all uploaded combo gallery images.

7. **Related combos or individual pieces**

8. **Related products**

9. **Related categories**

10. **Last viewed products**

---

## 5. Related Product, Related Category, and Last Viewed Requirements

Both service and combo product details pages must show these bottom sections:

1. Related products
2. Related categories
3. Last viewed products

Use the same card design language as the premium product details page.

### Recommended order

For service page:

1. Related products / services
2. Related categories
3. Last viewed

For combo page:

1. Related combos / individual products
2. Related products
3. Related categories
4. Last viewed

Make sure the sections are not visually too heavy.
Use clean cards, small badges, price, and CTA.

---

## 6. Smart Combo Upsell / Marketing Feature

Add a marketing feature that recommends relevant combos when a user is viewing or adding an individual product.

This is a win-win strategy:

- Customer feels they are saving money.
- Store can increase average order value.
- Premium products feel more attractive.

### Where to show combo upsell

#### A. Individual product details page

If the current product is included in one or more combos, show a section:

```text
Complete the set and save more
```

Show combo cards with:

- Combo image
- Combo name
- Included item count
- Regular total
- Combo price
- Save percentage
- Save amount
- CTA: View combo

Example copy:

```text
This item is part of Nikkah Combo. Get 12% off when you take the full set.
```

#### B. Fallback when this product has no direct combo

If the product is not part of any combo, show combos from the same category or related categories.

Section copy:

```text
Premium combos you may love
```

or

```text
Save more with curated bridal combos
```

Logic:

1. First priority: combos that include this exact product.
2. Second priority: combos from the same category.
3. Third priority: combos from related categories.
4. Fourth priority: featured combos.

This helps inspire customers to buy a premium product even if the current product is not inside a combo.

#### C. Cart page

If cart contains an individual product that belongs to a combo, show a strong upgrade card:

```text
You can save BDT 546 by switching to Nikkah Combo
```

Show:

- Current selected product
- Related combo
- Current cart item price
- Combo regular total
- Combo discounted price
- Savings amount
- CTA:
  - Upgrade to combo
  - Keep current item

If the cart already has multiple products from the same combo, make the message stronger:

```text
You already selected 2 items from this combo. Add the full combo and save 12%.
```

#### D. Mini cart / add-to-cart drawer

After a product is added to cart, show a small premium upsell block:

```text
This item is part of a curated combo. Upgrade and save 12%.
```

CTA:

- View combo
- Upgrade to combo

### Design of upsell section

Use a premium highlighted card:

- Warm ivory background
- Soft border
- Deep red savings badge
- Combo image on left
- Text and pricing on right
- Small included item thumbnails
- Clear CTA button

Do not make this section look like spam or aggressive sales.
It should feel helpful and inspiring.

---

## 7. Admin Panel Improvements

The admin panel currently does not have enough customization options for service and combo products.
Improve the product editor while keeping the current tab-based structure.

### Service tab improvements

Add fields:

#### Basic service info

- Service type
- Duration label
- Location scope
- Starting price / fixed price mode
- Advance amount
- Requires advance payment checkbox
- Confirmation note

#### Booking configuration

- Available areas
- Available days
- Time slot options
- Minimum notice period
- Max bookings per day
- Travel outside area allowed checkbox
- Extra charge note

#### Rich content sections

Support editable sections for:

- What this service includes
- Available packages / scopes
- Booking flow
- Before appointment
- Pricing and extra charges
- Policy
- FAQ
- Gallery intro text

Use repeatable fields where needed.
For example:

- Include item title
- Include item description
- Icon optional
- Sort order

### Rich text editor

Replace plain large textarea for long service details with a rich text editor.

Acceptable options:

- Tiptap
- TinyMCE
- CKEditor
- Markdown editor

Minimum formatting support:

- Headings
- Bold
- Bullet list
- Numbered list
- Links
- Paragraph spacing

Sanitize output before rendering on frontend.

### Combo tab improvements

For each combo child product, admin should be able to configure:

- Product select
- Quantity
- Required / optional
- Default included variant
- Allowed variants
- Variant change allowed checkbox
- Combo discount eligible checkbox
- Excluded premium upgrade checkbox
- Price mode:
  - Included in combo price
  - Add child product price
  - Custom combo price
  - Upgrade price only
- Display label
- Sort order
- Show on hero included strip checkbox
- Show in full combo details checkbox

### Combo pricing admin fields

Add fields:

- Discount type: percent / fixed
- Discount value
- Rounding rule
- Enable live recalculation checkbox
- Show savings badge checkbox
- Promo headline
- Promo subtitle
- Highlight as recommended combo checkbox

### Product relation admin fields

Add fields for marketing and related content:

- Related products
- Related categories
- Related combos
- Show related combos on product page checkbox
- Show related combos in cart checkbox
- Priority order
- Marketing label:
  - Save 12%
  - Best value
  - Most gifted
  - Complete set

---

## 8. Multiple Image / Gallery Requirement

Currently, service and combo products allow multiple images in admin, but frontend does not show them properly.
Fix this.

### Required behavior

- Primary image should be used as hero image.
- Gallery images should appear as thumbnails and in carousel.
- Service page should show service gallery images.
- Combo page should show combo gallery images.
- Related product cards should use only the primary image.
- Last viewed cards should use only the primary image.

### Admin image labels

Support image labels if possible:

- Hero
- Gallery
- Detail section
- Lifestyle
- Included item preview

---

## 9. Data / Backend Expectations

Make the implementation clean and reusable.

Use existing product type logic if available:

- Regular product
- Service / Booking
- Bundle / Combo
- Advanced customization / Nikahnama

Add migrations only if necessary.
Do not break existing products.

### Suggested tables / fields if needed

You may add or modify tables for:

- service product meta
- combo product meta
- combo child items
- combo child variant rules
- product related combos
- product gallery labels
- product FAQs
- product content sections

Keep names consistent with the current project conventions.

---

## 10. Frontend Behavior

### Dynamic combo price

When a customer changes a child variant in a combo:

- Update subtotal
- Update discount amount
- Update final combo price
- Update savings text
- Update CTA price if shown

This can be done with Alpine.js, Vue, Livewire, or existing frontend approach used in the project.
Choose the approach that fits the current codebase best.

### Cart upsell logic

When cart contains a product:

- Check direct related combos first.
- If no direct combo exists, check combos from same category.
- Then related categories.
- Then featured combos.

Show the best 1 to 3 combo suggestions.

Avoid showing duplicate combos.
Avoid showing out-of-stock or unpublished combos.

---

## 11. UX Copy Examples

Use polished copy like this:

### Product page combo upsell

```text
Complete the set and save more
This item is part of a curated bridal combo. Get better value when you take the full set.
```

### Fallback combo upsell

```text
Premium combos you may love
Explore curated bridal sets designed to make your order feel complete while helping you save more.
```

### Cart upgrade

```text
You can save BDT 546
Switch to Nikkah Combo and get 12% off on this curated set.
```

### Combo pricing explanation

```text
Your combo discount is calculated from the selected included items. Eligible variant upgrades are included in the discount calculation unless marked as premium excluded upgrades.
```

### Service booking explanation

```text
Submit your preferred date, time, area, and service notes. Our team will review availability and confirm the booking before requesting advance payment.
```

---

## 12. Acceptance Criteria

The task is complete when:

### Service page

- Service page follows the premium product details layout.
- Left side image/gallery is sticky on desktop.
- Multiple uploaded images are visible.
- Booking form is cleaner and less visually heavy.
- Service information is shown in structured sections.
- Related products, related categories, and last viewed products are shown.

### Combo page

- Combo page follows the premium product details layout.
- Left side image/gallery is sticky on desktop.
- Multiple uploaded images are visible.
- All included child products are shown, not only two.
- Child product variants are clearly shown.
- Dynamic percent-based pricing works.
- Savings amount and percentage are displayed clearly.
- Related products, related categories, and last viewed products are shown.

### Marketing upsell

- Individual product page shows related combos.
- If direct combo does not exist, fallback combo recommendations from category/related category/featured combos appear.
- Cart page shows smart combo upgrade suggestions.
- Mini cart or add-to-cart drawer shows compact combo upsell if available.

### Admin panel

- Service products have better structured customization options.
- Combo products have child product and variant configuration options.
- Rich text editor or structured rich content support exists for long service/combo details.
- Admin can manage combo discount percentage, rounding, savings badge, and marketing labels.

### Code quality

- No existing product details page should break.
- Existing regular and Nikahnama product flows should keep working.
- Use reusable components where possible.
- Keep Blade/components clean and maintainable.
- Add clear comments only where logic is complex.

---

## 13. Final Goal

The final result should make Azraq feel like a premium bridal ecommerce brand where:

- Service products feel trustworthy and easy to book.
- Combo products feel clear, valuable, and premium.
- Customers understand exactly what they are getting.
- Customers feel happy about saving money through curated combos.
- The store benefits from better cross-sell and higher average order value.
