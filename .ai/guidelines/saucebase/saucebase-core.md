## Saucebase

Saucebase is a modular Laravel SaaS starter kit. Treat the implementation and
manifests as authoritative; do not infer dependency versions or tool settings
from prose.

### Sources of Truth

- Backend dependencies and constraints: `composer.json`
- Static-analysis configuration: `phpstan.neon`
- Vue stack: `stubs/saucebase/stack/vue/package.json`
- React stack: `stubs/saucebase/stack/react/package.json`
- Module behavior: `app/Providers/ModuleServiceProvider.php`,
  `module-loader.js`, and the recipe stubs

The root `package.json` is framework-neutral before stack selection. Do not use
it alone to determine the supported Vue or React dependencies.

### Module Conventions

Modules are copy-and-own Composer packages installed under lowercase
`modules/<name>/` directories. PHP namespaces remain TitleCase.

An installed Composer module is active; there is no enable/disable toggle.
Never bypass `module-loader.js` for module assets, translations, or Playwright
project discovery.

Every main module provider extends `App\Providers\ModuleServiceProvider`. Do not
add `$name` or `$nameLower`: the base provider resolves the module name through
`ModuleRegistry::moduleForClass()`.

Use lowercase module identifiers in frontend checks such as
`modules().has('auth')`.

Module migrations are named `0000_00_00_NNNNNN_<action>_table.php`, numbered
in dependency order within the module. A module ships its whole schema at
install, so the date carries no meaning; only the order does. The app's
`users` table is `0000_00_00_000000` and therefore always runs first. Modules
may depend on `users` and nothing else today; a module that gains a foreign
key to another module's table takes the next day, `0000_00_01_`, so it sorts
after everything it depends on.

Traits live in a `Traits/` directory, never `Concerns/` — the name says what the
file is. This holds for internal helpers too (`Filament/Traits/`,
`Console/Traits/`), not only the ones a host model uses.

### Frontend Conventions

Saucebase supports both Vue and React. Apply shared frontend infrastructure
changes to both implementations.

In contributor mode, edit the real sources under `resources/js/vue/` and
`resources/js/react/`. Do not edit generated root entry-point passthroughs or
generated TypeScript declarations.

Framework-neutral code shared by both stacks lives in a `lib/` directory —
`resources/js/lib/` in the app (imported as `@js/lib/...`) and
`resources/js/lib/` in a module — never `utils/`, matching `vue/lib/` and
`react/lib/`.

Format dates with `formatDate()` from `@js/lib/dates`, passing the app's language
(`useLocalization().language` in Vue, `useTranslation().locale` in React) —
never `toLocaleDateString()` directly, and never `page.props.locale`, which goes
stale when the language switcher changes it without a page load.
`formatDateTime()` is for pages that never render on the server.

All components must support light and dark themes. Use stable `data-testid`
attributes for E2E selectors; never select translated text, labels, or role
names. Item-specific selectors use `{action}-${item.id}`.

### Settings Modal

Account and workspace settings are one modal over the current page, addressed by
the URL fragment `#settings/<slug>`. There is no settings page, layout, or
sidebar route.

A module contributes a panel by putting an `App\Settings\SettingsSection`
subclass in its own `src/Settings` directory; `SectionRegistry` discovers it
there. Only the requested section's `props()` runs — the rest are
`Inertia::optional()` and resolve when the user switches to them.

Two rules the fragment imposes:

- **Links that open settings stay plain anchors.** Inertia's `Link` calls
  `preventDefault()` and writes history with `pushState`, so the browser never
  fires `hashchange` and nothing hears the fragment change. Use `settingsHref()`
  (`useSettingsModal`), never `Link`.
- **The modal's focus trap yields to overlays above it.** The modal traps focus
  with a document-level `focusin` listener, and so does every dialog, menu or
  popover portalled to the body, so the two would recurse until the stack
  overflows. `initializeModals()` in `resources/js/lib/modal.ts`, called
  once from each stack's `app` entry, swallows the event while the modal
  is `data-aria-hidden`. Panels and overlay primitives need no change; never fix
  this inside `components/ui/`, which the shadcn CLI regenerates.

Panels render a bare `space-y-8` block opening with a muted `p` description —
no `Card` shell, which the modal already provides — and carry a
`settings-<slug>-panel` test id. The title is not the panel's: the modal draws
the active section's title in its own header, so a panel that repeats it shows
it twice.

### Verification

Run the smallest relevant checks from `CONTRIBUTING.md`. Run module PHPUnit
tests with a 2048 MB PHP memory limit.

Update these source guidelines when a durable project convention changes, then
regenerate agent instructions with `composer boost:update`. Never edit the
generated Laravel Boost blocks in `AGENTS.md` or `CLAUDE.md` directly.
