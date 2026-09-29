---
paths:
  - '**/resources/js/**'
---

# Frontend

## Shared code goes in lib/
Framework-neutral code goes in `resources/js/lib/` (the app's is imported as `@js/lib/...`), never `utils/`. Vitest covers only `lib/**/*.test.ts` in the app and modules, so logic outside `lib/` gets no unit tests.

## Format dates with formatDate()
Use `formatDate()` from `@js/lib/dates` with the app's language (`useLocalization().language` in Vue, `useTranslation().locale` in React). `toLocaleDateString()` ignores the app's language, and `page.props.locale` goes stale when the language switcher changes it without a page load. `formatDateTime()` is only for pages that never render on the server.

## Icons
Use the stack's Lucide package for everyday UI icons, as the shadcn components do. For any other Iconify set, import `~icons/<set>/<name>` (e.g. `~icons/simple-icons/github`); `unplugin-icons` compiles it to a component, so there's no need to add SVG files or another icon library. `autoInstall` adds a missing `@iconify-json/<set>` package on first import; commit that `package.json` change.

Navigation items name their icon by string. Register the component with `registerIcon(name, Component)` from `@/lib/navigation` in the module's `setup()`. `resolveIcon()` returns `undefined`, with no warning, for a name nobody registered.

## Aliases
`vite.config.js` defines `@` for the app's frontend code, `@js` for `resources/js`, `@css` for `resources/css`, and `@modules` for `modules/`. Built chunks and assets from `modules/<name>/` are emitted under `assets/<name>/`.
