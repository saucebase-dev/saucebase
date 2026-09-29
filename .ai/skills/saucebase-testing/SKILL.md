---
name: saucebase-testing
description: Choose, write, and run Saucebase tests — PHPUnit (app and modules), Vitest for lib/ code, and Playwright E2E. Use when adding or changing behavior, writing tests, or deciding which checks to run.
---

# Saucebase Testing

Write the failing test first, then the code that makes it pass.

## Which suite

| Change | Suite | Location |
|---|---|---|
| PHP behavior in the app | PHPUnit `Feature` / `Unit` | `tests/Feature`, `tests/Unit` |
| PHP behavior in a module | PHPUnit `Modules` | `modules/<name>/tests/{Feature,Unit}` |
| Framework-neutral TypeScript | Vitest | `**/resources/js/lib/**/*.test.ts` only |
| Components, pages, user flows | Playwright | `tests/e2e`, `modules/<name>/tests/e2e` |

Vitest runs in a Node environment with no Vite plugins, so it can't render components; Playwright covers those.

## Commands

```bash
php artisan test --compact tests/Feature/SomeTest.php
php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\\Auth\\Tests'
npm run test:unit
npx playwright test --project="@auth*"     # one module; core specs are "@Core*"
```

Run module suites with a 2048 MB memory limit, as `CONTRIBUTING.md` does.

Playwright project names are `@<module> [<device>]`, one per installed module,
so filter with a trailing `*`. Every project depends on
`database.setup`; a module can add setup projects in its own
`playwright.config.ts`.

## PHPUnit base class

`Tests\TestCase` seeds the database (`$seed = true`) and forces Vite to the
build manifest, so a missing manifest entry fails locally as it does in CI.
`createUser()` returns a user with the `user` role.

## Playwright

Specs import `test` and `expect` from `@e2e/fixtures`; see `.ai/rules/e2e-tests.md`.
Helpers in `tests/e2e/helpers`: `loginAs`, `expectAuthenticated`, `expectGuest`
(`auth.ts`), `isModuleInstalled` (`modules.ts`), `expectSSREnabled`,
`expectSSRDisabled`, `expectInertiaPageDataEmbedded` (`ssr.ts`).

`tests/Support` is the PHP side of those helpers: Playwright calls
`AuthHelper`, `TestFixtures`, and `ModuleSupport` through `laravel.callFunction()`.
`TestFixtures::seedSharedAccounts()` runs once in `database.setup`, because
seeding per test races between parallel workers; `credentials()` creates
fresh admin and user accounts for each test.

## Isolating tests that touch files

A test that runs commands writing to the application root (Boost, installers)
must point `setBasePath()` at a temporary directory, `chdir()` into it, and
bind a fake `ModuleRegistry`, so the real `vendor/`, `boost.json`, and
`CLAUDE.md` are never touched. `tests/Feature/ModuleBoostIntegrationTest.php`
is the reference.

## Before finishing

```bash
vendor/bin/pint --dirty --format agent
composer analyse
npm run lint
```
