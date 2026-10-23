---
name: saucebase-frontend-development
description: Build Saucebase frontend features — Inertia pages in the app or a module, module setup hooks, navigation icons and actions, global component slots, auth modals, and settings-modal links. Use when adding or changing pages, components, navigation, or module frontend wiring.
---

# Saucebase Frontend Development

Also read `.ai/rules/frontend.md` before editing under `resources/js/`.

## Pages

`Inertia::render('Dashboard')` resolves `pages/Dashboard` in the app.
`Inertia::render('Roadmap::Index')` resolves a module page: the part before
`::` is the module name, converted from TitleCase to its kebab-case directory
(`modules/roadmap/resources/js/pages/Index`).

## Module setup hooks

Each module's `resources/js/<framework>/app` entry (`resources/js/app` once
installed) may export `setup()` and `afterMount()`. Vue entries are `app.ts`,
React entries `app.tsx`; core finds no other name.
Core imports every module's entry eagerly and runs all `setup()` calls before
the first render, then all `afterMount()` calls after the browser mounts. The
server renders the same app, so `setup()` runs there too: keep it to
registrations and put anything that touches `window`, `document` or storage in
`afterMount()`. Register everything a module contributes in `setup()`:

| Call | Purpose |
|---|---|
| `registerIcon(name, Component)` (`@saucebase/<framework>/lib/navigation`) | Icon for navigation items whose `icon` attribute is `name` |
| `registerAction(id, handler)` (`@saucebase/<framework>/lib/navigation`) | Click handler for navigation items whose `action` attribute is `id`; registering an id twice warns and replaces the first |
| `registerGlobalComponent(slot, Component, { order? })` (`@saucebase/<framework>/lib/globalComponents`) | Component rendered in a layout slot |

Slots are named `{area}::{region}.{position}`, like Filament's render hooks.
Areas: `app` (shell), `site` (public layout), `dashboard` (signed-in layout),
`home` (index page). `before`/`after` sit outside a region, `start`/`end`
inside it. The full list is `GlobalComponentSlots` in `@saucebase/core/types`:
`app::page.*`, `site::{header,content,footer}.*`, `home::content.*`,
`dashboard::{header,content}.*` and `dashboard::sidebar.{start,end}`.
`dashboard::sidebar.brand` replaces the brand above the sidebar navigation;
`dashboard::user-menu.subtitle` replaces the email in the user menu. Pass
`{ order }` (default 100, lower first) to sort components in a slot. A module
adds its own slot by augmenting `GlobalComponentSlots` and rendering
`<GlobalComponents position="…" />`.

## Server rendering

Pages that opt into SSR render on the server, then the browser hydrates that
HTML instead of rendering again. The first browser render must match it:

- Read `window`, `document` and storage in `onMounted()` / `useEffect()`, never
  during setup or render; guard shared helpers with `typeof window`.
- Teleport or portal to `<body>` only after mount (`useMounted()` from VueUse,
  a `mounted` state in React): the server can't render into `<body>`.
- Derive what renders from page props, which are the same on both sides.

## What a module may import

| From | What | Notes |
|---|---|---|
| `@saucebase/core/lib/*` | `dates`, `initials`, `translations`, `settings`, `themeReveal`, `modal` | Framework-neutral. Ships with `saucebase/core`. |
| `@saucebase/vue/composables/*` or `@saucebase/react/hooks/*` | `useAuth`, `useHttp`, `useLocalization`, `useModules`, `useSettings`, `useSettingsModal`, `useDialog` | |
| `@saucebase/<framework>/lib/*` | `navigation`, `globalComponents`, `pages`; `language` (Vue); `toastActions` (React) | |
| `@saucebase/react/i18n` | `useT`, `useTranslation`, `I18nProvider` | Vue uses `laravel-vue-i18n` directly. |
| `@/components/*`, `@/layouts/*`, `@/lib/utils` (`cn`) | The app's own UI: shadcn primitives, layouts | The user owns these and may change them. |
| `@modules/<name>/resources/js/...` | Another module's code | Use it sparingly: the import breaks when that module is not installed. |

Never import a `vendor/` path, and never reach into the app's pages. The
`@saucebase/*` names are aliases the Vite preset in `saucebase/core` defines for its
`resources/js`; only the app's own framework has one.

## Navigation

Items are declared in PHP (`routes/navigation.php`); see
`saucebase-module-development`. Their `icon` and `action` names resolve through
the registries above. The `landing` group renders in the public header.

## Auth modals

Login and register open as a modal only when the link is a `ModalLink`, which
sends the modal header; see the auth module's skill. A plain `Link` to the same
URL renders the page.

## Settings modal links

Open settings with a plain anchor to `settingsHref(section)` from
`useSettingsModal`. An Inertia `Link` writes history with `pushState`, so no
`hashchange` fires and the modal never opens.

## Checks

```bash
npm run lint
npm run format:check
npm run build
```

User-facing changes need an E2E spec; see `saucebase-testing`.
