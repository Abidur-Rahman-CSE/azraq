# Azraq Bridal — Nikah Nama Template, Mockup, and Product Flow Prompt

## Objective
Build a production-ready Nikah Nama personalization system for Azraq Bridal inside the existing Laravel + Blade + Tailwind + Alpine stack.

The system must separate three concerns cleanly:

1. **Nikah Nama Template Layer**
   This is the flat certificate design layer.
   It defines the fixed artwork ratio, text zones, font presets, and preview/export rules.

2. **Mockup Layer**
   This is the lifestyle presentation layer.
   Admin uploads a frame or interior mockup scene and maps where the Nikah Nama composite should appear inside the frame area.

3. **Product Layer**
   When creating a Nikah Nama product, admin selects:
   - which Nikah Nama template is used
   - which mockups are available on the storefront gallery
   - which gallery order to show

The final user journey should feel simple:
- customer opens the Nikah Nama product page
- customer sees real product gallery including template and selected mockups
- customer fills structured personalization fields
- customer chooses a font preset
- customer sees live preview on the flat Nikah Nama
- gallery also shows scene mockups using the same Nikah Nama composite
- customer adds to cart
- admin later reviews the structured payload and final proof data

---

## Brand And UI Foundation
Use the Azraq Bridal guideline as the visual base:
- primary typography: **Poppins**
- brand colors: `#780000`, `#C1121F`, `#FDF0D5`, `#003049`, `#669BBC`
- favicon path: `storage/images/logo/Azraq.svg`
- logo treatment: icon on the left, brand text on the right, visually balanced horizontal lockup

Use a premium bridal editorial look:
- warm ivory page background
- deep navy headings
- restrained maroon as primary action/accent
- soft rounded cards
- subtle border and depth
- elegant spacing, not crowded
- premium but not flashy

---

## Architectural Decision
### Important Rule
Do **not** make text individually configurable inside each mockup.

Instead:
- the **Nikah Nama template** owns all text zones
- the **mockup** only owns the placement of the whole rendered Nikah Nama rectangle inside a scene

That means:
1. admin creates the flat Nikah Nama layout once
2. admin defines text positions on that rectangle once
3. system renders one flat composite
4. mockup layer warps or maps that composite into the selected scene

This is the correct design because:
- it is easier to manage
- it avoids duplicating text config per mockup
- it keeps proof/export reliable
- it scales when adding new mockups

---

## Product Type Rules
Implement clear storefront product types:

### 1. standard_product
For normal products like bridal accessories, mirror, badge, bangles.
No advanced personalization.

### 2. light_customizable_product
For items like customized pen or simple bridal wear notes.
Can have simple text input, but not full Nikah Nama template logic.

### 3. advanced_personalized_product
For Nikah Nama and Premium Nikah Nama.
This type uses:
- assigned template
- assigned mockups
- structured personalization fields
- live preview
- proof notes

### 4. service_booking_product
For bridal / mehendi booking items.
Uses booking flow, not inventory-style product logic.

### 5. bundle_product
For combo/package products.
Uses bundle logic.

---

## Core Data Model Expectations
Implement the following logical entities.

### A. products
Fields include:
- id
- type
- title
- slug
- short_description
- description
- price
- compare_price
- featured_image
- gallery_images
- category_id
- collection_id nullable
- inventory_mode
- status
- is_featured
- meta_title
- meta_description

### B. categories
Fields include:
- name
- slug
- parent_id nullable
- thumbnail_image
- banner_image nullable
- short_description nullable
- sort_order
- status

### C. nikah_templates
Fields include:
- name
- slug
- assigned_product_id nullable
- base_image
- preview_image nullable
- export_ratio_width
- export_ratio_height
- safe_zone_notes nullable
- instructions nullable
- proof_note_label nullable
- status

### D. nikah_template_fields
Each field belongs to a template.
Fields include:
- template_id
- key
- label
- placeholder
- type
- is_required
- default_value nullable
- max_chars nullable
- text_align
- color
- x
- y
- width
- height
- rotation
- min_font_size
- max_font_size
- line_height
- letter_spacing
- font_weight nullable
- z_index
- preview_sample_value nullable
- sort_order

Coordinates are normalized within the flat certificate canvas.
Use 0 to 1 values.

### E. nikah_font_presets
Fields include:
- template_id
- name
- label
- css_font_family
- preview_class nullable
- is_default
- sort_order
- status

### F. nikah_mockups
Fields include:
- title
- slug
- assigned_template_id nullable
- base_image
- thumbnail_image nullable
- render_mode
- fit_mode
- frame_ratio_lock boolean
- artwork_ratio_width
- artwork_ratio_height
- top_left_x
- top_left_y
- top_right_x
- top_right_y
- bottom_right_x
- bottom_right_y
- bottom_left_x
- bottom_left_y
- opacity
- shadow_strength
- highlight_strength
- sort_order
- is_active
- notes nullable

### G. nikah_product_mockup_map
Pivot mapping between advanced personalized products and allowed mockups.
Fields include:
- product_id
- mockup_id
- sort_order
- is_default

### H. product_images
For all products, including normal products.
Fields include:
- product_id
- image_path
- alt_text nullable
- type (featured, gallery, mockup_preview, template_preview)
- sort_order

---

## Admin Experience — High Level
### Admin must have these pages

1. Product index
2. Product create/edit
3. Category index
4. Category create/edit
5. Nikah Template index
6. Nikah Template create/edit
7. Nikah Template field editor
8. Nikah Font preset manager
9. Nikah Mockup index
10. Nikah Mockup create/edit
11. Nikah Product assignment section inside product form
12. Personalized order review page

---

## 1. Product Create/Edit Page Requirements
This page is currently missing important things.
Add them properly.

### General section
- title
- slug
- product type
- category
- collections
- short description
- full description
- featured / published state
- pricing
- compare price

### Image section
Must support:
- featured image upload
- multiple gallery images upload
- image reorder
- remove image
- drag-sort gallery order
- image caption / alt optional

### Category section
Allow:
- primary category
- optional secondary tags
- show related categories on storefront

### Nikah-specific section (only if product type = advanced_personalized_product)
Show this section conditionally.

Fields:
- assigned Nikah template dropdown
- selectable mockup list with thumbnail previews
- drag-sort selected mockups
- choose default gallery image source:
  - template flat preview
  - selected mockup
  - manual featured image
- toggle whether flat preview appears first in gallery
- toggle whether mockup thumbnails appear in product gallery
- toggle whether proof notes are enabled
- toggle whether live preview is shown on storefront

### Related merchandising section
For all products:
- related products manual selector
- related categories selector
- recommended combos selector
- cross-sell items

### Storefront preview sidebar
Right side sticky preview showing:
- product card preview
- price block preview
- main image preview
- Nikah Nama mockup summary if assigned

---

## 2. Category Create/Edit Page Requirements
Category page must support images.

Fields:
- name
- slug
- parent category
- thumbnail image
- banner image
- short description
- storefront excerpt
- sort order
- active status

UI:
- left: form
- right: live category card preview
- below: collection preview strip

Storefront usage:
- thumbnail image for category cards
- banner image for category landing hero

---

## 3. Nikah Template Editor — Flat Certificate Builder
This is the most important editor.

### Main goal
Admin uploads the flat Nikah Nama base design and defines where each text field appears.

### Required layout
Use a premium two-column admin layout.

#### Left main area
Large live template canvas.
Show the flat Nikah Nama image at a fixed aspect ratio.
Canvas must feel like a design surface.

Inside the canvas:
- show base certificate image
- overlay text boxes using sample values
- update live as fields change
- toggle field bounds visibility
- select active field visually

#### Right configuration sidebar
Stacked cards:
1. Template identity
2. Base assets
3. Ratio and export rules
4. Field list
5. Font presets
6. Preview sample data
7. Save actions

### Template rules
- the certificate has a **fixed ratio**
- admin chooses ratio once, e.g. 9:13 or another exact print ratio
- all text fields are placed relative to this fixed rectangle
- mockups later must respect this ratio

### Field editor behavior
Each text field should support:
- label
- field key
- placeholder
- required toggle
- max chars
- sample preview value
- alignment
- color
- normalized x/y
- normalized width/height
- min/max font size
- line height
- letter spacing
- rotation if needed
- z-index

### Better UX for field placement
Add both methods:
- direct drag/resize on canvas
- numeric controls in sidebar

### Font preset UI
Show curated font cards with preview text.
Admin can:
- add font preset
- choose preview label
- set default
- reorder

### Preview sample data
Admin can change sample values for bride, groom, date, venue so the canvas always previews realistic content.

---

## 4. Nikah Mockup Editor — Frame Placement System
This is the area where the biggest clarification is needed.

### Correct behavior
Admin uploads a **frame scene or mockup scene**.
Then admin defines where the whole Nikah Nama rectangle should sit inside that frame.

The admin is **not** editing text here.
The admin is only placing the rendered certificate composite.

### Required editor layout
Use a two-column layout.

#### Left main area
Large live preview area.
This is the primary workspace.

Order inside left column:
1. mockup live preview card
2. coordinate editor directly below the preview
3. before/after comparison card

This matches your requested workflow better than keeping coordinate editor far away.

### Live preview behavior
When admin uploads the frame image:
- show the uploaded image immediately
- overlay the flat Nikah Nama preview inside it
- preserve the certificate ratio
- allow perspective placement via 4 corners
- show border guides and control handles

### Coordinate editor placement
Place the coordinate editor **under the live preview**, not far away.
This is important for usability.

### Coordinate editor controls
Show these fields:
- top left x, y
- top right x, y
- bottom right x, y
- bottom left x, y
- fit mode
- preview opacity
- reset mapping
- snap to rectangle
- load test certificate

### Keyboard interaction
When a corner is selected:
- arrow keys move selected corner by small step
- shift + arrow moves by bigger step
- alt + arrow moves by micro step
- active corner should be visually obvious
- selection should sync with numeric inputs

### Ratio rule
The Nikah Nama itself must always keep a predetermined ratio.
So:
- placement points define the transformed quadrilateral
- the source artwork is always a rectangle with fixed ratio
- the text inside the source artwork remains relative to that source rectangle
- after mapping, the whole composite is warped together

This solves the “text reshape automatically” requirement correctly.
The system should **not** re-lay text independently inside the mockup.
It should transform the full rendered certificate.

### Asset inputs for mockup
Support:
- base image upload
- thumbnail upload
- optional overlay image upload
- optional mask upload only if needed later

But for current phase, keep it simple.
The core requirement is:
- frame scene upload
- mapped placement area
- live preview

### Render recommendation
For the current scope, prioritize:
- `perspective_quad`

Optional support later:
- `flat_fit`
- `masked_perspective`

### Before/after card
Under the preview, show:
- flat certificate preview
- mapped mockup preview

This helps QA instantly.

---

## 5. Nikah Product Flow — Recommended Final System
Your proposed product flow is good and should be implemented.

### Product creation flow for Nikah Nama
When admin creates a Nikah Nama product:
1. choose product type = advanced_personalized_product
2. choose assigned Nikah template
3. choose allowed mockups from existing mockup library
4. sort which mockups appear in gallery
5. choose whether flat template preview appears first
6. save

### Why this is good
Because it keeps the user journey easy:
- template defines the actual certificate
- product decides which template is sold
- product decides which presentation mockups are shown
- storefront automatically renders both flat and mockup views

This is scalable and clean.

---

## 6. Storefront Nikah Nama Product Page — Required UX
The Nikah Nama PDP must feel premium and complete.
Do not make it look like a generic product page.

### Desktop layout
Use a two-column layout.

#### Left column
Product media gallery.
Include:
- flat template preview
- selected mockup scenes
- optional close-up detail shots
- thumbnail rail
- main media area

Main media should support:
- previous/next controls
- thumbnail click
- active state
- if live preview enabled, first image updates based on current personalization input

#### Right column
Purchase and personalization panel.

Order:
1. category breadcrumbs
2. product title
3. price
4. short trust copy
5. font selector
6. structured text fields
7. proof note textarea
8. add to cart
9. wishlist
10. delivery / proof notes accordion

### Related content below PDP
Must include these sections:

#### A. Related products
Editorial product cards.
Show 4 or 6 items.
Use same visual language as homepage product cards.

#### B. Related categories
Category cards with image and short copy.

#### C. How it works
3 or 4 step process.

#### D. FAQ
Focused on proof, delivery, personalization, corrections.

### Live preview logic
As customer types:
- flat template preview updates
- if gallery is showing a mapped mockup preview, mockup should also update if technically feasible
- otherwise update the main flat preview immediately and keep mockups static until blur or throttle refresh

Recommended performance strategy:
- immediate flat preview update
- throttled mockup preview refresh

---

## 7. Rendering Logic
### Source rendering order
Use this pipeline:

1. render flat Nikah Nama composite from template + chosen font + field values
2. use that rendered composite as the source artwork
3. map source artwork into mockup scene using the selected 4-corner coordinates
4. display composed output in admin preview and storefront gallery preview

### Why this is correct
Because:
- text stays consistent
- template remains authoritative
- mockup remains presentation-only
- adding new scenes becomes easy

---

## 8. UI/UX Refinements For Admin Mockup Editor
Keep most of your current direction. Only adjust what is necessary.

### Keep
- premium neutral background
- large workspace
- right-side metadata/settings structure
- live test certificate copy
- 4-corner mapping approach

### Change
- move coordinate editor below preview
- make preview area larger and visually dominant
- make selected corner state much clearer
- add keyboard help chip directly under preview
- show ratio lock badge
- show “template ratio: 9:13 locked” info
- remove confusing free-transform feeling
- make it obvious the artwork is a locked-ratio source rectangle being mapped into the frame

### Add small but important UI details
- top bar with save state
- unsaved changes indicator
- zoom reset button
- fit to frame button
- duplicate mockup button
- compare toggle
- keyboard nudging hint

---

## 9. Exact Page-Level Prompt For Codex
Use this as the implementation prompt.

---

### FULL PROMPT START
Build a premium Laravel Blade + Tailwind + Alpine admin and storefront implementation for Azraq Bridal’s Nikah Nama personalization system.

Use the existing Azraq brand language:
- font: Poppins
- colors: #780000, #C1121F, #FDF0D5, #003049, #669BBC
- favicon path: storage/images/logo/Azraq.svg
- logo lockup: icon on the left, brand text on the right in a clean horizontal header treatment

Implement the following system:

#### A. Product types
Create structured support for:
- standard_product
- light_customizable_product
- advanced_personalized_product
- service_booking_product
- bundle_product

Only `advanced_personalized_product` uses the Nikah Nama personalization engine.

#### B. Product create/edit page
Add a full product form with:
- title, slug, price, compare price, description, short description
- featured image upload
- multi-image gallery upload with reorder
- category selector
- related products selector
- related categories selector
- combo recommendations selector
- published/featured status

If product type is `advanced_personalized_product`, show an additional section with:
- assigned Nikah template select
- selected mockups multi-select with thumbnails
- drag-sort selected mockups
- choose whether flat template preview appears first in the gallery
- choose whether mockup previews are included in storefront gallery
- enable proof note toggle
- enable live preview toggle

#### C. Category create/edit page
Add support for:
- name, slug, parent category
- category thumbnail image upload
- category banner image upload
- short description
- storefront excerpt
- sort order and active state

#### D. Nikah template editor
Create a premium admin page where the left side contains a large fixed-ratio flat certificate preview canvas and the right side contains stacked settings cards.

Support:
- base template image upload
- preview image upload optional
- template ratio width and height
- instructions
- proof note label
- safe zone notes
- live sample preview
- field list editor
- font presets manager
- preview sample data manager

Each template field must support:
- label
- key
- placeholder
- required toggle
- max chars
- alignment
- color
- x, y, width, height using normalized values
- rotation
- min and max font size
- line height
- letter spacing
- z-index
- preview sample value

Allow dragging and resizing fields directly on the preview canvas and syncing numeric values in the sidebar.

#### E. Nikah mockup editor
Create a premium admin page for uploading a frame scene and mapping where the Nikah Nama composite will appear.

Important rules:
- the Nikah Nama source artwork is always a fixed-ratio rectangle
- the mockup editor does not control text placement
- the mockup editor only controls the mapped placement of the whole rendered certificate composite

Page layout:
- left main column: large live mockup preview
- directly below preview: coordinate editor
- below that: before/after comparison
- right sidebar: identity, assignment, asset uploads, notes, save actions

Mockup editor features:
- upload base scene image
- upload thumbnail image optional
- upload overlay image optional
- assign template
- render mode default = perspective_quad
- active toggle
- sort order
- ratio lock indicator
- test certificate preview

Coordinate editor features:
- top left x/y
- top right x/y
- bottom right x/y
- bottom left x/y
- fit mode
- preview opacity
- reset mapping
- fit to frame button
- load test preview button

Interaction features:
- click a corner to select it
- arrow keys nudge selected corner
- shift + arrow moves faster
- alt + arrow moves slower
- active corner is strongly highlighted
- changes sync to numeric fields

Preview behavior:
- after frame upload, show live composite immediately
- map the flat rendered Nikah Nama into the scene using 4-corner perspective placement
- preserve the base certificate ratio
- show visible guide border and handles

#### F. Nikah product storefront page
Create a premium personalized product page for Nikah Nama items with:
- breadcrumbs
- title
- price
- editorial product gallery on the left
- structured personalization form on the right
- font selection cards
- proof note textarea
- add to cart and wishlist buttons
- trust microcopy
- how it works section
- related products section
- related categories section
- FAQ section

Gallery logic:
- include the flat template preview
- include the selected mockups assigned to the product
- update live preview for the flat certificate immediately as customer types
- optionally refresh mapped mockup previews with throttling

#### G. Rendering logic
Use a two-stage render pipeline:
1. generate a flat Nikah Nama composite from template + fields + chosen font
2. map that composite into the mockup quadrilateral area

Do not place text independently inside each mockup.
The template remains the single source of truth for text zones.

#### H. Visual design rules
Use a premium bridal admin UI:
- ivory base background
- deep navy headings
- maroon primary CTA
- rounded xl cards
- light borders
- elegant shadows
- generous spacing
- calm hierarchy

Use a premium storefront UI:
- warm editorial surfaces
- clean product cards
- large readable headings
- subtle trust chips
- premium but minimal controls

#### I. Technical expectations
- Laravel Blade components for reusable cards, fields, media sections, and admin panels
- Tailwind utility styling
- Alpine for editor interactions and gallery state
- normalized coordinate storage
- clean separation between template layer, mockup layer, and product layer
- product images stored cleanly with featured + gallery distinction
- Nikah Nama assets can be sourced from storage/images/Nikahnama and future uploads

#### J. Deliverables
Implement page-by-page:
1. category create/edit page with images
2. product create/edit page with gallery uploads and Nikah settings
3. Nikah template editor
4. Nikah mockup editor
5. Nikah product storefront page
6. related products and related categories sections

Focus on UI/UX polish and production-ready component structure rather than placeholder-only layouts.

### FULL PROMPT END

---

## Final Recommendation
The guide you generated is already conceptually strong.
It correctly separates:
- product layer
- template layer
- mockup layer

That part should stay. The main improvement is this:

- make the mockup editor explicitly about **frame placement of the full Nikah Nama rectangle**
- keep all text field control in the template editor
- let product creation choose which template and which mockups appear on the storefront

That is the cleanest version of the system.
