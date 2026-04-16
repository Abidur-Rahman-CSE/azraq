# Design Tokens

## Active Foundation
The current foundation stores tokenized styling in:
- `config/brand.php`
- `resources/css/tokens.css`
- `resources/css/components.css`

## Core Color Tokens
- `--color-primary-900`
- `--color-primary-700`
- `--color-surface-cream`
- `--color-secondary-900`
- `--color-secondary-500`
- `--color-text-main`
- `--color-text-soft`
- `--color-border-soft`

## Layout And UI Tokens
- spacing scale from `--space-1` to `--space-24`
- radius scale from `--radius-sm` to `--radius-pill`
- shadow tokens for cards, hover, modal, and focus
- container tokens from `--container-sm` to `--container-2xl`
- typography tokens aligned to the Poppins-led brand system

## Rules
- Prefer semantic tokens over raw hex values in components.
- Shared UI classes belong in `resources/css/components.css`.
- New storefront/admin UI should be built from reusable Blade components first, not page-local styling.
