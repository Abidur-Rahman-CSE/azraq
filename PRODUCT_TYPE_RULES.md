# Product Type Rules

## Supported Types
1. `standard`
2. `light_customizable`
3. `advanced_personalized`
4. `bundle`
5. `service`

## Rules By Type
- Standard products support variants, stock, pricing, gallery, related products, reviews, shipping, and SEO.
- Light customizable products add a few simple input fields and optional style selections, but do not use the heavy Nikah Nama builder.
- Advanced personalized products use templates, text zones, font rules, preview images, and structured personalization payloads.
- Bundle products point to child SKUs and derive sellability from child stock.
- Service products capture booking and location preferences instead of inventory-led purchasing rules.

## Important Constraints
- Do not apply Nikah Nama personalization rules globally.
- Do not mix service booking logic into physical stock deduction.
- Do not represent bundles as fake single-SKU products.
