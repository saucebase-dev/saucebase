---
name: saucebase-{module-}-development
description: Work on the {Module } module (modules/{module-}). Use when changing its routes, models, pages, or tests.
---

# {Module } Module

Paths are relative to `modules/{module-}/` unless they say otherwise. Commands run from the application root.

## Key Files

| Layer | Files |
|-------|-------|
| Provider | `{Module}ServiceProvider` |
| Controller | `{Module}Controller` |
| Filament | `{Module}Plugin` |

## Testing

```bash
php artisan test --testsuite=Modules --filter='^Modules\\{Module}\\Tests'  # PHPUnit
npx playwright test --project="@{module-}*"                               # E2E
```
