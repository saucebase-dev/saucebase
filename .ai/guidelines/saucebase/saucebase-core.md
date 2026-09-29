## Saucebase

Saucebase is a modular Laravel SaaS starter kit. Treat the implementation and
manifests as authoritative; do not infer dependency versions or tool settings
from prose.

### Sources of Truth

- Backend dependencies and constraints: `composer.json`
- Static-analysis configuration: `phpstan.neon`
- Frontend dependencies: `package.json`
- Module behavior: `Saucebase\Core\Providers\ModuleServiceProvider` (the
  `saucebase/core` package),
  `module-loader.js`, and the recipe stubs

### Module Conventions

Module conventions come from the `saucebase/core` guideline. In the app,
never bypass `module-loader.js` for module assets, translations, or Playwright
project discovery, and use lowercase module identifiers in frontend checks
such as `modules().has('auth')`.

### Frontend Conventions

If both `resources/js/vue/` and `resources/js/react/` exist, this is a
contributor checkout: follow `CONTRIBUTING.md` before changing any frontend code.

All components must support light and dark themes.

### Settings Modal

Account and workspace settings are one modal over the current page, addressed by
the URL fragment `#settings/<slug>`. There is no settings page, layout, or
sidebar route.

Sections come from `SettingsSection` subclasses (see
`saucebase-module-development`). Only the requested section's `props()` runs;
the rest are `Inertia::optional()` and resolve when the user switches to them.

Two rules the fragment imposes:

- **Links that open settings stay plain anchors.** Inertia's `Link` calls
  `preventDefault()` and writes history with `pushState`, so the browser never
  fires `hashchange` and nothing hears the fragment change. Use `settingsHref()`
  (`useSettingsModal`), never `Link`.
- **The modal's focus trap yields to overlays above it.** The modal traps focus
  with a document-level `focusin` listener, and so does every dialog, menu or
  popover portalled to the body, so the two would recurse until the stack
  overflows. `initializeModals()` in `resources/js/lib/modal.ts`, called
  once from the `app` entry, swallows the event while the modal
  is `data-aria-hidden`. Panels and overlay primitives need no change; never fix
  this inside `components/ui/`, which the shadcn CLI regenerates.

### Where to Look

- Module structure, provider, navigation, settings sections, seeders:
  `saucebase-module-development` (from `saucebase/core`)
- Filament: `saucebase-filament-development` (from `saucebase/core`)
- Pages, navigation, module frontend wiring: `saucebase-frontend-development`
- Which tests to write and run: `saucebase-testing`
- Path-specific rules (migrations, frontend, E2E, settings panels, shadcn):
  `.ai/rules/index.md`

### Verification

Write the failing test before the change. Before finishing, run the affected
tests, `vendor/bin/pint --dirty --format agent`, and `composer analyse`.

Run the smallest relevant checks from `CONTRIBUTING.md`. Run module PHPUnit
tests with a 2048 MB PHP memory limit.

Update these source guidelines when a durable project convention changes, then
regenerate agent instructions with `composer boost:update`. Never edit the
generated Laravel Boost blocks in `AGENTS.md` or `CLAUDE.md` directly.
