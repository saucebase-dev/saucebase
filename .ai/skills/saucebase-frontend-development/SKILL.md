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

Each module's `resources/js/app` entry may export `setup()` and `afterMount()`.
Core imports every module's entry eagerly and runs all `setup()` calls before
the app mounts, then all `afterMount()` calls after it. Register everything a
module contributes in `setup()`:

| Call | Purpose |
|---|---|
| `registerIcon(name, Component)` (`@/lib/navigation`) | Icon for navigation items whose `icon` attribute is `name` |
| `registerAction(id, handler)` (`@/lib/navigation`) | Click handler for navigation items whose `action` attribute is `id`; registering an id twice warns and replaces the first |
| `registerGlobalComponent(slot, Component)` (`@/lib/globalComponents`) | Component rendered in a layout slot |

Slots: `top` and `bottom` wrap the page; `sidebar-brand` replaces the block
above the sidebar navigation; `user-subtitle` replaces the email under the
user's name in the sidebar user menu. A new slot needs a case in
`globalComponents` and a render in the layout that owns that region.

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
