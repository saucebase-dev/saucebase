---
name: saucebase-module-development
description: "Guides Saucebase module creation and development. Activate when scaffolding a new module, adding controllers/pages/migrations to a module, working with module service providers, Filament module plugins, or when user mentions saucebase:recipe, modules:list, or asks about module structure."
license: MIT
metadata:
  author: saucebase
---

# Saucebase Module Development

Before writing any code, run the pre-flight interview below. Ask questions one at a time. Questions marked *(if frontend)* are only asked when question 1 is answered with "frontend pages".

---

## Pre-Flight Interview

**Q1. Does this module have frontend pages (Inertia/Vue) or is it admin-only (Filament)?**
→ Answer gates the rest of the interview.

**Q2. Does it need models and migrations?**

**Q3. Does it need Filament resources?**

**Q4. *(if frontend)* Does it have public/SEO pages that need SSR?**

**Q5. Does it need a database seeder?**

**Q6. *(if frontend pages in the logged-in area)* Does it need navigation entries and breadcrumbs?**

**Q7. *(if frontend)* Should we write E2E tests?**

**Q8. Do you want to use TDD? If yes, activate `/tdd` before writing any implementation code.**

Once all answers are collected, proceed to scaffolding.

---

## Scaffolding

```bash
php artisan saucebase:recipe ModuleName
```

Choose **Basic Recipe** when prompted.

---

## Post-Scaffold Checklist

Run these after every scaffold, in order:

```bash
composer dump-autoload && php artisan package:discover
npm run build   # or restart `npm run dev`
```

Then apply answers from the pre-flight:

- **Admin-only module** → clear `routes/navigation.php` completely (never leave a `route()` call to a non-existent route)
- **No frontend pages** → delete the scaffolded Vue page and skip E2E setup
- **Has seeder** → add `db:seed` task to `Taskfile.yml` (`php artisan modules:seed --module=<name>`)
- **Has E2E tests** → scaffold `tests/e2e/index.spec.ts` using `data-testid` selectors only
- **Uses TDD** → write failing tests before any implementation; activate `/tdd`
