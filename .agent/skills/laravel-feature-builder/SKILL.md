---
name: laravel-feature-builder
description: Build or modify Laravel features using existing routes, controllers, models, migrations, Blade views, validation, authorization, and project conventions. Use when the task is about Laravel feature development.
---

# Laravel Feature Builder

When working on a Laravel feature:

1. First inspect existing related files:
   - routes/web.php
   - routes/api.php if relevant
   - app/Models
   - app/Http/Controllers
   - resources/views
   - database/migrations

2. Follow the existing project style and naming conventions.

3. Do not create duplicate logic if an existing service, model relation, component, or helper already exists.

4. For database changes:
   - Create safe migrations.
   - Use short custom index names when needed.
   - Avoid destructive changes unless explicitly requested.

5. For forms:
   - Add validation.
   - Preserve old input on validation errors.
   - Show useful error messages.

6. For Blade/Tailwind:
   - Reuse existing components.
   - Keep the UI responsive.
   - Follow the existing design language.

7. Before finishing, check:
   - php artisan route:list if routes changed
   - php artisan migrate --pretend if migrations changed
   - php artisan test if tests exist
   - pnpm build if frontend assets changed

8. Final response must include:
   - changed files
   - what was implemented
   - commands run
   - manual test checklist
