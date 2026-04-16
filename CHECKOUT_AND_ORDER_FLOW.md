# Checkout And Order Flow

## Core Flow
1. Customer adds a standard, customizable, personalized, bundle, or service product to cart.
2. Cart stores structured item data by product type.
3. Checkout captures address, contact, shipping, and payment data.
4. Order creation stores order items plus subtype-specific payloads.
5. Payment and fulfillment statuses progress independently.

## Required Order Item Support
- standard item pricing and variant data
- bundle composition snapshot
- Nikah Nama personalization snapshot
- booking request details for service items

## Status Separation
- `payment_status`
- `fulfillment_status`
- `personalization_status`
- `booking_status` when applicable

## Future Work
- guest checkout decision
- coupon engine
- shipping rules by zone
- manual proof approval for personalized products
