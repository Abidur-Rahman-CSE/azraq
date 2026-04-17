
# Azraq Bridal — Admin Panel Upgrade README
## Product Media, Category Images, Nikah Nama Template + Mockup Mapping System

This README is a **supplemental admin-panel prompt pack** for Codex.

It fixes the missing parts from the current admin foundation:
- product image upload is missing
- category image upload is missing
- Nikah Nama template + mask + mockup placement is incomplete
- mockup angle/position control is missing
- dynamic mockup addition is missing
- order-side proof and preview workflow is incomplete

This file is **admin-panel only**.
User panel is not covered here except where preview dependencies are required.

---

# 0. Core direction

Build the admin as a **clean, premium, modular control panel** for Azraq Bridal.
The admin must support these product types:

1. **standard_product**
   - normal catalog items
   - example: Bridal Mirror, Badge, Bangles

2. **light_customizable_product**
   - simple custom text only
   - example: Customized Pen, basic label customization

3. **advanced_personalized_product**
   - template-driven personalized design with structured fields
   - example: Nikah Nama, Premium Nikah Nama, Nikah Booklet

4. **service_booking_product**
   - booking-based products
   - example: Bridal / Non-Bridal / Mehndi Booking

5. **bundle_or_combo_product**
   - grouped purchasable sets
   - example: Nikah Combo, Bridal Gift Combo, Mehendi Combo

The admin must not treat all products the same.
**Nikah Nama products must have a specialized personalization architecture.**

---

# 1. Brand and UI system for admin

Use the Azraq Bridal brand language consistently.

## 1.1 Typography
- Primary font: `Poppins`
- Use medium to semibold for headings
- Use regular to medium for admin form labels
- Avoid decorative fonts in admin except inside font preview cards

## 1.2 Core colors
- Primary deep maroon: `#780000`
- Strong accent red: `#C1121F`
- Warm ivory background: `#FDF0D5`
- Deep navy text / anchor color: `#003049`
- Soft supporting blue: `#669BBC`

## 1.3 Admin surface usage
- Main background: very soft warm neutral derived from `#FDF0D5`
- Cards: white or ivory-white
- Primary CTA: `#780000`
- Secondary CTA / links: `#003049`
- Focus ring / subtle chips: `#669BBC`
- Danger state: slightly deeper red derived from `#C1121F`

## 1.4 Design tone
- calm
- premium
- editorial
- uncluttered
- rounded but not playful
- clean border hierarchy
- generous spacing
- strong section labels

---

# 2. Required admin modules to add or upgrade

## 2.1 Product management upgrade
Must add:
- featured image upload
- multi-image gallery upload
- drag-to-sort gallery
- alt text per image
- mark image as primary
- image labels like `front`, `detail`, `lifestyle`, `mockup`, `size-guide`
- image delete and replace
- gallery preview grid
- upload progress
- image crop suggestion for thumbnail
- optional video URL field
- product-type-specific sections

## 2.2 Category management upgrade
Must add:
- category image upload
- category banner image upload
- mobile banner optional
- category icon optional
- image alt text
- sort order
- featured category toggle
- homepage visibility toggle
- related category linking
- SEO image

## 2.3 Nikah Nama personalization module
Must add:
- template manager
- fields manager
- font preset manager
- proof notes handling
- preview data presets
- live preview support
- template image upload
- template mask upload
- mockup manager
- mockup mapping editor
- mockup status and sort order
- per-template mockup assignment

## 2.4 Mockup rendering module
Must add:
- upload new mockup image
- assign to a Nikah Nama template
- define inner artwork placement area
- define angle / perspective
- define overlay shadow/highlight layers
- define mask or clipping area
- toggle active/inactive
- preview rendered result
- duplicate mockup
- set thumbnail and sort order

---

# 3. Recommended asset structure

Do not hardcode everything in templates.
Use database-driven records plus structured storage paths.

## 3.1 Logo and favicon
- favicon path: `storage/images/logo/Azraq.svg`
- logo lockup in admin header/sidebar:
  - icon on the left
  - text on the right
  - top small label: `AZRAQ BRIDAL`
  - main line: `Admin Foundation` or context-specific section title

## 3.2 Nikah Nama asset folders
Recommended:
- `storage/images/Nikahnama/templates/`
- `storage/images/Nikahnama/masks/`
- `storage/images/Nikahnama/mockups/base/`
- `storage/images/Nikahnama/mockups/overlays/`
- `storage/images/Nikahnama/mockups/thumbs/`
- `storage/images/Nikahnama/previews/`

If legacy files already exist in `storage/images/Nikahnama`, support importing them from there first, then normalize them into structured subfolders.

---

# 4. Data model requirements

## 4.1 products
Important fields:
- id
- title
- slug
- type
- short_description
- description
- sku
- price
- compare_price
- stock_mode
- status
- featured_image
- category_id
- collection_id nullable
- personalization_template_id nullable
- is_featured
- is_customizable
- is_active

## 4.2 product_images
Fields:
- id
- product_id
- image_path
- alt_text
- label
- sort_order
- is_primary
- status

## 4.3 categories
Fields:
- id
- parent_id nullable
- title
- slug
- description
- image_path
- banner_path
- mobile_banner_path nullable
- icon_path nullable
- alt_text nullable
- sort_order
- is_featured
- show_on_homepage
- status

## 4.4 personalization_templates
Fields:
- id
- product_id
- title
- slug
- base_template_path
- mask_path nullable
- preview_image_path nullable
- default_mockup_id nullable
- proof_help_text nullable
- is_active

## 4.5 personalization_fields
Fields:
- id
- personalization_template_id
- field_key
- field_label
- field_type
- placeholder
- help_text
- max_length
- is_required
- sort_order
- visibility_rules_json nullable

## 4.6 personalization_fonts
Fields:
- id
- personalization_template_id
- title
- font_family
- font_file_path nullable
- preview_label
- is_default
- sort_order
- status

## 4.7 personalization_zones
Each text/image placement zone inside the Nikah Nama template.

Fields:
- id
- personalization_template_id
- field_key
- zone_type
- x
- y
- width
- height
- rotation
- text_align
- vertical_align
- font_size_min
- font_size_max
- letter_spacing
- line_height
- color
- z_index
- rules_json nullable

## 4.8 personalization_mockups
Fields:
- id
- personalization_template_id
- title
- slug
- base_image_path
- overlay_image_path nullable
- mask_image_path nullable
- thumb_image_path nullable
- render_mode
- sort_order
- is_active
- notes nullable

## 4.9 personalization_mockup_maps
This is the most important table for angle and placement.

Fields:
- id
- personalization_mockup_id
- map_type
- top_left_x
- top_left_y
- top_right_x
- top_right_y
- bottom_right_x
- bottom_right_y
- bottom_left_x
- bottom_left_y
- normalized_coordinates boolean default true
- fit_mode
- object_position_x nullable
- object_position_y nullable
- manual_rotation nullable
- shadow_strength nullable
- highlight_strength nullable
- opacity nullable

Store coordinates as **normalized values between 0 and 1** relative to the mockup image width and height.

That makes the mockup dynamic and resolution-independent.

---

# 5. How Nikah Nama placement should work

This is the key implementation requirement.

## 5.1 Basic idea
The personalized Nikah Nama final artwork is first rendered as a clean flat image.
Then that image is placed into a lifestyle mockup frame.

## 5.2 Recommended flow
1. Customer data creates a **flat personalized certificate render**
2. Admin or storefront preview selects a **mockup**
3. System reads the mockup mapping settings
4. System transforms the flat certificate into the inner frame area
5. System clips it to the frame area using mask
6. System applies shadow / highlight / glass / texture overlay if available
7. Final mockup image is generated for preview or order proof

## 5.3 For straight mockups
Use:
- x
- y
- width
- height
- optional rotation

Good for:
- near-flat frames
- front-facing posters

## 5.4 For angled or perspective mockups
Use **4-corner mapping**:
- top-left
- top-right
- bottom-right
- bottom-left

Good for:
- shelf mockups
- side-angle frames
- perspective lifestyle scenes

This is better than a single angle field.

## 5.5 Why 4-corner mapping is best
Because “angle” alone is not enough.
A realistic frame placement often needs:
- scale
- skew
- perspective distortion
- slight asymmetry
- vertical and horizontal correction

So the admin must support **dragging 4 corners** of the placement quad over the frame interior.

---

# 6. Recommended render modes

Each mockup should support one of these modes:

## 6.1 flat_fit
- simple rectangular fit
- no perspective
- optional rotation

## 6.2 perspective_quad
- warp flat artwork into a quadrilateral
- best for realistic mockups
- recommended default for Nikah Nama lifestyle images

## 6.3 masked_perspective
- same as perspective quad
- plus a mask or alpha clipping image
- best for premium quality output

Use `masked_perspective` for production-grade Nikah Nama mockups.

---

# 7. How to support masks inside storage/images/Nikahnama

The admin must support existing mask assets from `storage/images/Nikahnama`.

## 7.1 What a mask does
A mask defines **where the certificate may appear**.
Anything outside the mask is hidden.

## 7.2 How to use it
For each mockup:
- base image = room/lifestyle photo
- mapped certificate = transformed personalized artwork
- mask image = defines visible artwork area
- overlay image = optional shadows, glass reflections, top frame details

Render order:
1. base image
2. transformed certificate
3. mask clip
4. overlay image

## 7.3 Dynamic support
Admin must allow:
- upload mask
- replace mask
- preview mask on top of mockup
- toggle mask visibility in editor
- set mask opacity while editing
- save without mask if the mockup is simple

---

# 8. Mockup editor requirements

This page is mandatory.

## 8.1 Editor layout
Two-column layout:

### Left column
- large mockup editor canvas
- background mockup image
- toggle preview certificate
- draggable 4 corner handles
- zoom in / zoom out
- reset mapping
- show safe bounds
- show overlay toggle
- show mask toggle
- opacity slider for preview layer

### Right column
- mockup metadata form
- title
- slug
- assigned template
- render mode
- upload base image
- upload mask image
- upload overlay image
- thumbnail
- active status
- sort order
- notes
- coordinate inputs for manual fine-tuning
- save button
- duplicate button
- delete button

## 8.2 Canvas behavior
- use a contained editor viewport
- preserve image aspect ratio
- allow drag handles
- allow keyboard nudge
- allow manual input for each corner value
- display normalized coordinate values live
- show rendered mini preview after save

## 8.3 Visual editing tools
Must support:
- snapping guides
- display of top-left / top-right / bottom-right / bottom-left points
- preview of transformed Nikah Nama image
- mask outline
- optional highlight/shadow overlay preview

---

# 9. Best technical implementation for mockup placement

## 9.1 Admin-side editor
Use a browser canvas layer for editor interaction.

Recommended options:
- Konva.js
- Fabric.js
- or a custom HTML canvas editor

## 9.2 Storefront preview
For quick live preview:
- HTML/CSS or canvas preview is acceptable

## 9.3 Final saved preview / proof / export
Use server-side generation.

Recommended:
- ImageMagick / Imagick
- or a dedicated image pipeline service
- or Python imaging worker if project architecture allows

## 9.4 Best practice
- browser preview for editing convenience
- server-side render for reliable final image

---

# 10. Page index for Codex execution

Use this order when asking Codex to build page by page.

1. Admin shell / sidebar / topbar
2. Dashboard
3. Products index page
4. Product create page
5. Product edit page
6. Categories index page
7. Category create/edit page
8. Collections index + editor
9. Personalization templates index
10. Personalization template editor
11. Mockup manager index
12. Mockup editor page
13. Order personalization review page

---

# 11. Detailed page requirements and prompts

---

## Page 1 — Admin shell / sidebar / topbar

### Purpose
Build the main admin layout.

### Must include
- left sidebar
- topbar
- logo lockup using icon-left + text-right
- section-aware page title
- search placeholder optional
- profile dropdown
- responsive collapse
- breadcrumb area

### Sidebar items
- Dashboard
- Products
- Categories
- Collections
- Tags
- Personalization
- Mockups
- Inventory
- Orders
- Bookings
- Homepage
- FAQs
- Pages
- Coupons
- Settings

### Codex prompt
```text
Create the Azraq Bridal admin shell using Laravel Blade, Tailwind-style utility classes, and Alpine.js where useful. Use a premium warm-neutral interface with Poppins typography. Build a left sidebar, sticky topbar, responsive content area, breadcrumb slot, and reusable card/page-header components. Use the Azraq logo lockup with icon on the left and text on the right. The favicon path is storage/images/logo/Azraq.svg. Sidebar items must include Dashboard, Products, Categories, Collections, Tags, Personalization, Mockups, Inventory, Orders, Bookings, Homepage, FAQs, Pages, Coupons, and Settings.
```

---

## Page 2 — Dashboard

### Must include
- KPI cards
- low stock alert
- recent orders
- pending proofs
- active templates count
- active mockups count
- category quick stats
- top products
- action shortcuts

### Special cards
- personalized orders waiting for proof
- mockups missing mask
- products without gallery image
- categories without image

### Codex prompt
```text
Build the Azraq Bridal admin dashboard as a premium operational overview. Include KPI cards for orders, revenue, personalized orders, low stock items, active templates, and active mockups. Add alert panels for products without gallery images, categories without category images, personalized orders pending proof, and mockups missing mapping or masks. Keep the layout clean, card-driven, and responsive.
```

---

## Page 3 — Products index page

### Must include
- searchable table/grid
- product thumbnail
- product title
- type badge
- category
- price
- stock
- status
- image count
- has personalization template yes/no
- actions dropdown

### Filters
- product type
- category
- status
- stock status
- customizable only
- Nikah Nama only

### Bulk actions
- publish
- unpublish
- archive
- assign category
- mark featured

### Codex prompt
```text
Build the Products index page for Azraq Bridal admin. Show product thumbnail, title, type, category, price, stock, image count, personalization template status, and active status. Add filters for product type, category, customizable items, Nikah Nama products, and stock status. Use a polished table/card hybrid layout optimized for catalog operations.
```

---

## Page 4 — Product create page

### Purpose
This page must fix the current missing image-upload problem.

### Sections
1. Basic information
2. Pricing
3. Product type
4. Category and collection
5. Media uploads
6. Product details
7. Personalization settings
8. Inventory
9. SEO
10. Publish settings

### Media upload requirements
- featured image uploader
- multi-image gallery uploader
- drag reorder
- image label selector
- primary image selection
- live gallery preview
- replace/remove actions
- alt text inputs
- allowed formats and size info

### Product type logic
#### standard_product
No personalization section

#### light_customizable_product
Show basic custom text config

#### advanced_personalized_product
Show template assignment + proof workflow settings

#### service_booking_product
Show booking-only form fields

#### bundle_or_combo_product
Show bundle items builder

### Codex prompt
```text
Build the Product create page for Azraq Bridal admin with modular sections. The page must include featured image upload, multi-image gallery upload, drag-and-drop sorting, alt text per image, image labels such as front/detail/lifestyle/mockup/size-guide, and primary image selection. Add conditional form sections based on product type: standard_product, light_customizable_product, advanced_personalized_product, service_booking_product, and bundle_or_combo_product. For advanced_personalized_product, include personalization template assignment and proof workflow settings.
```

---

## Page 5 — Product edit page

### Must include everything from create page plus:
- existing media gallery with reorder
- upload additional images
- replace existing image
- delete image
- image usage labels
- preview storefront card
- preview product page media strip
- related products picker
- related categories picker

### Nikah Nama-specific additions
- assign default mockup set
- assign default template
- enable customer proof notes
- enable font presets
- preview personalization entry point

### Codex prompt
```text
Build the Product edit page for Azraq Bridal admin. Include a complete editable media gallery with upload, reorder, replace, delete, alt text, and usage labels. Add storefront preview blocks for card thumbnail and product page gallery. For Nikah Nama products, add fields for assigning default personalization template, default mockup set, proof note support, font presets, and related products.
```

---

## Page 6 — Categories index page

### Must include
- category image thumbnail
- title
- parent category
- product count
- featured toggle
- homepage toggle
- status
- quick edit
- sort order

### Alerts
- categories without image
- categories without banner
- empty categories

### Codex prompt
```text
Build the Categories index page for Azraq Bridal admin with thumbnail, title, parent, product count, featured status, homepage visibility, sort order, and actions. Include alert rows or summary chips for categories missing category images or banners. Keep the layout elegant and fast to scan.
```

---

## Page 7 — Category create/edit page

### Must include
- title
- slug
- description
- parent category
- category image upload
- banner image upload
- mobile banner upload optional
- icon upload optional
- alt text
- featured toggle
- homepage toggle
- related categories
- SEO fields

### Image preview behavior
- preview cards for all uploaded assets
- replace/remove controls
- recommended size helper text

### Codex prompt
```text
Build the Category create/edit page for Azraq Bridal admin. Include category image upload, banner image upload, optional mobile banner, optional icon, alt text, parent category selection, featured toggle, homepage toggle, related categories, and SEO fields. Show visual preview cards for uploaded images with replace and remove actions.
```

---

## Page 8 — Collections index + editor

### Must include
- collection cover image
- title
- product count
- manual/automatic type
- active status
- sort order
- featured toggle

### Editor
- assign products
- rule-based inclusion optional
- cover image upload
- description
- CTA label optional

### Codex prompt
```text
Build the Collections index and collection editor for Azraq Bridal admin. Add cover image upload, collection metadata, manual or automatic collection mode, featured toggle, active status, and product assignment UI. Keep it visually consistent with the rest of the admin.
```

---

## Page 9 — Personalization templates index

### Must include
- template thumbnail
- template title
- assigned product
- number of fields
- number of fonts
- number of mockups
- active status
- edit action

### Extra quick stats
- templates missing base image
- templates missing fields
- templates missing mockups

### Codex prompt
```text
Build the Personalization Templates index page for Azraq Bridal admin. Show template thumbnail, title, assigned product, total fields, total fonts, total mockups, status, and actions. Add summary alerts for templates missing base image, fields, or mockups.
```

---

## Page 10 — Personalization template editor

### Must include
- base template image upload
- template mask upload
- preview image upload
- field manager
- font preset manager
- proof notes helper text
- live sample preview
- safe zone editor
- assign default mockups
- preview data preset inputs

### Field manager
For each field:
- key
- label
- placeholder
- help text
- max length
- required toggle
- sort order

### Zone manager
For each field:
- x
- y
- width
- height
- rotation
- min/max font size
- line height
- letter spacing
- text color
- alignment

### Codex prompt
```text
Build the Personalization Template Editor for Azraq Bridal admin. Include base template image upload, optional mask upload, preview image, field manager, font preset manager, proof helper text, and a safe-zone editor. Each field must support a linked render zone with x, y, width, height, rotation, font sizing rules, line height, letter spacing, color, and alignment. Show a live sample preview using demo bride name, groom name, date, and venue values.
```

---

## Page 11 — Mockup manager index

### Must include
- mockup thumbnail
- title
- assigned template
- render mode
- mask yes/no
- overlay yes/no
- status
- sort order
- edit action

### Quick warnings
- missing mapping
- missing mask
- inactive mockups
- no thumbnail

### Codex prompt
```text
Build the Mockup Manager index page for Azraq Bridal admin. Show each mockup with thumbnail, title, assigned template, render mode, mask availability, overlay availability, status, sort order, and actions. Add warning states for incomplete mockups, especially missing mapping or missing thumbnail.
```

---

## Page 12 — Mockup editor page

### This is the most important page.

### Must include
#### Left side
- large interactive editor canvas
- base mockup image
- optional mask overlay
- optional highlight overlay
- transformed Nikah Nama preview
- draggable 4 corner handles
- line connectors between handles
- opacity controls
- zoom controls
- reset controls
- load test certificate preview

#### Right side
- title
- assigned template
- render mode
- base image upload
- mask image upload
- overlay image upload
- thumbnail upload
- active status
- sort order
- manual coordinate fields
- fit mode
- shadow strength
- highlight strength
- notes
- save / duplicate

### Required editor features
- normalized coordinate saving
- manual numeric edit
- keyboard nudge for selected corner
- toggle between `flat_fit`, `perspective_quad`, `masked_perspective`
- save current preview
- show before/after comparison
- test using one of the uploaded blank or rendered Nikah Nama samples

### Angle / positioning logic
Do not store only `angle`.
Store:
- top_left_x, top_left_y
- top_right_x, top_right_y
- bottom_right_x, bottom_right_y
- bottom_left_x, bottom_left_y

This allows:
- skew
- tilt
- perspective
- realistic placement

### Codex prompt
```text
Build the Azraq Bridal Mockup Editor page as an advanced admin tool for Nikah Nama placement. The page must provide a large interactive canvas with a lifestyle mockup image on the left and a settings panel on the right. Allow admins to upload a base mockup image, optional mask image, optional overlay image, and a thumbnail. The editor must support dragging 4 corner handles over the frame interior so the flat personalized Nikah Nama artwork can be warped into that quadrilateral. Save coordinates in normalized 0-to-1 values. Add controls for render mode (flat_fit, perspective_quad, masked_perspective), preview opacity, shadow strength, highlight strength, zoom, reset mapping, and duplicate mockup. Show a live rendered preview using a sample Nikah Nama artwork.
```

---

## Page 13 — Order personalization review page

### Must include
- order summary
- product summary
- customer personalization inputs
- selected font
- proof notes
- generated flat certificate preview
- generated mockup preview
- approve / request change / regenerate proof
- assign mockup
- assign template
- internal note

### Codex prompt
```text
Build the Order Personalization Review page for Azraq Bridal admin. Show the ordered Nikah Nama product, customer personalization data, selected font, proof notes, flat certificate preview, and lifestyle mockup preview. Add controls for approving proof, requesting edits, regenerating preview, assigning a different mockup, or switching template. Keep the interface operational and calm.
```

---

# 12. Specific behavior for the three uploaded lifestyle mockups

Treat the uploaded blank room/frame images as starter mockups.

## Mockup-1
- nearly frontal
- soft warm lifestyle shot
- can work with rectangular fit or very mild perspective
- recommend `masked_perspective`
- slight shadow and gentle highlight preservation

## Mockup-2
- slight realistic perspective
- darker frame
- needs quadrilateral mapping
- recommend `perspective_quad` or `masked_perspective`

## Mockup-3
- stronger perspective / shelf composition
- definitely use quadrilateral mapping
- recommend `masked_perspective`
- artwork should sit inside the black frame and not bleed into frame edge

---

# 13. Example admin behavior rules

## 13.1 When product type is advanced_personalized_product
Show:
- template assignment
- font presets
- mockup set selection
- proof workflow
- sample preview button

Hide:
- irrelevant standard-only fields

## 13.2 When category has no image
Show warning in categories index.

## 13.3 When product has no gallery images
Show warning in products index and dashboard.

## 13.4 When mockup mapping is incomplete
Do not allow it to be the default mockup.

## 13.5 When template has no fields
Do not allow product to go live.

---

# 14. Non-negotiable requirements

- product images upload must exist
- category image upload must exist
- Nikah Nama template manager must exist
- Nikah Nama mockup manager must exist
- 4-corner mapping must exist
- masks from `storage/images/Nikahnama` must be supported
- new mockups must be addable from admin
- mockup preview must be testable before save
- coordinates must be resolution-independent
- admin UI must remain clean and premium

---

# 15. Final master Codex prompt

```text
Upgrade the Azraq Bridal admin panel into a production-ready catalog and personalization control center. Keep the premium warm-neutral brand language with Poppins typography and Azraq colors. Add missing product media management with featured image upload, multi-image gallery upload, drag sorting, alt text, image labels, and primary image selection. Add category media support including category image, banner image, mobile banner, and icon upload.

Build a specialized Nikah Nama personalization architecture for advanced personalized products. Add a Personalization Templates module with base template image upload, optional mask upload, fields manager, font presets, safe-zone editor, preview data presets, and linked render zones. Add a Mockup Manager and an advanced Mockup Editor page. The mockup editor must allow new mockups to be uploaded and mapped so the flat personalized Nikah Nama artwork can be placed inside the frame area of each lifestyle mockup. Support mask images from storage/images/Nikahnama and allow optional overlay images for shadows/highlights/frame details.

Do not rely on a single angle field. Implement 4-corner quadrilateral mapping with draggable handles and save normalized coordinates: top-left, top-right, bottom-right, bottom-left. Support render modes such as flat_fit, perspective_quad, and masked_perspective. Build the UI in a page-by-page modular way using Laravel Blade, Tailwind-style utility classes, and Alpine.js where useful. Create the following pages: admin shell, dashboard, products index, product create/edit, categories index, category create/edit, collections index/editor, personalization templates index, personalization template editor, mockup manager index, mockup editor, and order personalization review page.
```

---

# 16. Best next build order

Start in this order:
1. Admin shell
2. Product media support
3. Category image support
4. Personalization templates
5. Mockup manager
6. Mockup editor
7. Order proof review

That order will fix the biggest missing admin gaps first.
