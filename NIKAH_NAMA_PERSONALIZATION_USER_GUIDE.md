# Nikah Nama Personalization And Mockup User Guide

## Purpose
This guide explains how the Nikah Nama personalization system works in Azraq, including:
- what each personalization entity does
- how the customer-facing flow works
- how admin users set up templates and mockups
- how order review fits into the workflow

This guide is intended for store admins, operators, and anyone onboarding into the Nikah Nama workflow.

## Core Concepts

### Advanced Personalized Product
This is the storefront product type used for Nikah Nama items.

Examples:
- `Signature Nikah Nama`
- `Premium Nikah Nama`

These products do not use the normal simple product page flow. They use a structured personalization builder.

### Personalization Template
A personalization template defines the flat certificate setup.

It contains:
- the assigned product
- the base template image
- an optional preview image
- an optional mask image
- text field definitions
- font presets
- preview data presets
- proof rules and export settings

Think of the template as the flat editable certificate blueprint.

### Personalization Fields
Fields define the text zones the customer fills in.

Examples:
- bride name
- groom name
- ceremony date
- venue

Each field can define:
- label
- field key
- placeholder
- required state
- min and max font size
- text alignment
- text color
- position
- width and height
- rotation

### Font Presets
Font presets give customers curated typography choices for the Nikah Nama.

Each preset can define:
- internal name
- CSS font family
- preview label
- default state

### Mockup
A mockup is a lifestyle or presentation scene that shows how the finished Nikah Nama might look in context.

Examples:
- desk scene
- ceremony table scene
- framed layout scene

Each mockup can include:
- a base image
- an optional mask image
- an optional overlay image
- an optional thumbnail image
- a render mode
- a 4-corner mapped placement area

Think of the mockup as the styled presentation layer built on top of the flat template.

## How The Customer Flow Works

### 1. Customer Opens A Nikah Nama Product
The customer visits an advanced personalized product page such as `Signature Nikah Nama`.

The page shows:
- product gallery
- structured personalization form
- font options
- proof note area
- quantity and add-to-cart action

### 2. Customer Enters Personalization Data
The customer fills in the personalization fields.

Typical values:
- bride name
- groom name
- ceremony date
- venue

The customer may also:
- choose a font
- add proof notes for the designer

### 3. Customer Adds The Product To Cart
When the item is added to cart, Azraq stores a structured payload instead of plain free text.

That payload can include:
- selected template data
- field values
- selected font
- proof notes
- product and pricing context

### 4. Checkout Stores The Structured Order Item
During checkout, the personalization payload is saved onto the order item.

This keeps the Nikah Nama information separate from:
- payment status
- shipping status
- fulfillment status

### 5. Admin Reviews The Personalized Order
From the admin order area, the team can open the personalization review page and see:
- the customer inputs
- selected font
- proof notes
- flat preview
- mockup preview
- internal review notes

The team can then:
- approve
- request change
- regenerate proof

## Admin Setup Flow

### Step 1. Create Or Choose The Product
The Nikah Nama product must be a product of type `advanced_personalized`.

The product should already have:
- title
- price
- category
- storefront visibility
- product images

### Step 2. Create The Personalization Template
Go to:
- `Admin -> Personalization -> Templates`

Create or edit the template and configure:
- assigned product
- template name
- base template image
- preview image
- optional mask image
- instructions
- proof note label
- safe-zone notes
- preview and render rules

### Step 3. Define The Text Fields
In the same template editor, set up the fields.

For each field define:
- label
- field key
- default value
- color
- alignment
- x/y position
- width/height
- rotation
- font size bounds

The live sample preview on the right should help verify placement before saving.

### Step 4. Add Font Presets
Still inside the template, add the font presets the customer can choose from.

Keep this curated. Too many options make the storefront feel noisy.

### Step 5. Create Mockups
Go to:
- `Admin -> Mockups`

Create one or more mockups for the template.

Each mockup should include:
- title
- assigned template
- base image
- optional mask
- optional overlay
- optional thumbnail
- render mode

Then define the placement area using the 4-corner editor.

## How Mockups Work

### Flat Template vs Mockup
These two pieces serve different jobs.

Template:
- controls the actual editable certificate structure
- owns the text zones and font rules

Mockup:
- controls how the certificate appears in a styled scene
- owns the perspective or presentation mapping

### Render Modes
Current mockup render modes are:
- `flat_fit`
- `perspective_quad`
- `masked_perspective`

Use them like this:

`flat_fit`
- best for simple straight scenes
- easiest for clean proof visuals

`perspective_quad`
- best for scenes where the certificate sits at an angle
- uses 4-corner mapping

`masked_perspective`
- best when the mockup needs stronger scene masking or clipping
- useful when artwork must sit under foreground elements

### 4-Corner Mapping
The mockup editor lets the admin place:
- top left
- top right
- bottom right
- bottom left

These normalized coordinates define the placement zone where the flat Nikah Nama preview should appear.

This is used for:
- visual proofing
- scene testing
- final presentation logic

## Recommended Setup Order
For each new Nikah Nama design, follow this order:

1. Create the advanced personalized product.
2. Create the personalization template.
3. Upload the base template image.
4. Configure preview data presets.
5. Define all text fields.
6. Add font presets.
7. Confirm the live sample preview looks correct.
8. Create one or more mockups.
9. Upload the mockup base image.
10. Map the 4 corners.
11. Review the mockup preview.
12. Test a storefront add-to-cart flow.
13. Test the admin order personalization review flow.

## Practical User Flow

### Customer Journey
1. Customer visits the Nikah Nama product page.
2. Customer fills in the structured personalization fields.
3. Customer selects a font.
4. Customer adds proof notes if needed.
5. Customer adds the item to cart.
6. Customer completes checkout.
7. Order stores the personalization payload.

### Admin Journey
1. Admin opens the personalized order item.
2. Admin reviews the customer data.
3. Admin checks the selected template and mockup.
4. Admin verifies proof notes and spelling.
5. Admin marks the item for approval or revision.
6. Team proceeds with proofing and fulfillment.

## What The Live Preview Is For
The live preview inside the personalization template editor is meant to help admins:
- validate the sample artwork quickly
- check field placement
- spot overlap issues
- test color and alignment choices
- see how sample values read before saving

The live preview inside the mockup editor is meant to help admins:
- test the scene composition
- check mapped placement
- compare flat certificate vs lifestyle scene
- fine-tune the 4-corner area before saving

## Common Rules

### Do
- keep field keys stable once storefront data depends on them
- keep font options curated
- use mockups for presentation, not for core text layout logic
- verify the live sample preview before saving
- test at least one full add-to-cart flow after major template changes

### Avoid
- using one generic template for unrelated certificate designs
- creating too many tiny overlapping text zones
- relying only on mockups without checking the flat template
- adding extra fonts just because they are available
- changing field keys casually after orders already exist

## Troubleshooting

### Upload Does Not Work
Check:
- image size
- local PHP upload limit
- local Nginx body size
- `public/storage` symlink availability

### Preview Does Not Show
Check:
- base template image or preview image exists
- the file was selected correctly
- the field positions are within visible bounds

### Mockup Will Not Save
Check:
- base image is present
- assigned template is selected
- title is filled in
- mapping values are within `0` to `1`

### Order Review Looks Empty
Check:
- the product is using `advanced_personalized`
- the customer actually submitted personalization data
- the order item has the personalization payload

## Suggested QA Checklist
- product page loads
- personalization fields render correctly
- font selector works
- item adds to cart with structured payload
- checkout preserves personalization data
- template editor live preview updates correctly
- mockup editor live preview updates correctly
- mockup can be created and edited
- order personalization review page shows the expected values

## Summary
The Nikah Nama system in Azraq is built around three layers:

1. Product layer
   This is the advanced personalized storefront product.

2. Template layer
   This defines the flat certificate structure, fields, fonts, and preview rules.

3. Mockup layer
   This defines how the certificate appears in styled lifestyle scenes.

When these three layers are configured well, the user flow becomes:
- customer personalizes
- cart stores structure
- order preserves the payload
- admin reviews and proofs confidently
