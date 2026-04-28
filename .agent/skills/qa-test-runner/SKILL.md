---
name: qa-test-runner
description: Validate code changes by running relevant Laravel, PHP, frontend, build, route, migration, and manual QA checks. Use after implementing or modifying code.
---

# QA Test Runner

When validating changes:

1. Inspect what changed before choosing commands.

2. For Laravel backend changes, consider:
   - php artisan test
   - php artisan route:list
   - php artisan migrate --pretend
   - php artisan config:clear
   - php artisan view:clear

3. For frontend changes, consider:
   - pnpm build
   - pnpm lint if available
   - npm run build if project uses npm

4. For Blade/UI changes:
   - Check responsive layout.
   - Check dark/light theme if available.
   - Check empty state and validation error state.

5. Do not claim tests passed unless commands were actually run.

6. Final response must include:
   - commands run
   - result summary
   - remaining risks
   - manual test checklist
