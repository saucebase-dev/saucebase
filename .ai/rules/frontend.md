---
paths:
  - '**/resources/js/**'
---

# Frontend

## Shared code goes in lib/
Framework-neutral code goes in `lib/`, never `utils/`: Saucebase's own is in `saucebase/core` (`@saucebase/core/lib/...`), a module's in its `resources/js/lib/`. Vitest covers only `lib/**/*.test.ts` in modules (and core's `resources/js`), so logic outside `lib/` gets no unit tests.

## useHttp failures
Import `useHttp` from `@saucebase/vue/composables/useHttp` (Vue) or `@saucebase/react/hooks/useHttp` (React), not from `@inertiajs/*`. Inertia's `onError` fires for a 422 only; the wrapper shows any other failure (419, 500, no connection) as a toast and resolves `undefined` instead of rejecting, so check success in `onSuccess`, not after `await`. Pass a second argument to show the message elsewhere (a field's error, or a toast plus your own cleanup), or give one call its own `onHttpException`/`onNetworkError` to replace it.

## Format dates with formatDate()
Use `formatDate()` from `@saucebase/core/lib/dates` with the app's language (`useLocalization().language` in Vue, `useTranslation().locale` in React). `toLocaleDateString()` ignores the app's language, and `page.props.locale` goes stale when the language switcher changes it without a page load. `formatDateTime()` and `formatRelative()` ("yesterday", "in 3 weeks", via `Intl.RelativeTimeFormat`) are only for pages that never render on the server; pair a relative date with the exact one in a `title`.

## Keep logic out of templates
- No nested ternaries. Use early returns, or a lookup object keyed by the value (`{ active: …, revoked: … }[status]`).
- Templates bind values; they don't compute them. Move `.find()` lookups, multi-branch ternaries and repeated formatting (`date ? formatDate(date) : 'Never'`) into a named function or `computed`.
- Don't call the same function several times per list item in a template. Map the list once in a `computed` (Vue) or before rendering (React), and bind its fields.
- No `as never` / `as any` to quiet a type error. Type the value properly, or narrow it with one cast at the boundary where it's known to be valid.

## Icons
Use the stack's Lucide package for everyday UI icons, as the shadcn components do. For any other Iconify set, import `~icons/<set>/<name>` (e.g. `~icons/simple-icons/github`); `unplugin-icons` compiles it to a component, so there's no need to add SVG files or another icon library. The stacks install the eight sets Saucebase uses (`lucide`, `heroicons`, `simple-icons`, `fa-solid`, `solar`, `mdi`, `fluent`, `circle-flags`). For another set, run `npm i -D @iconify-json/<set>` and commit the `package.json` change; an icon from a set that isn't installed fails the build with "Icon not found".

Navigation items name their icon by string. Register the component with `registerIcon(name, Component)` from `@saucebase/vue/lib/navigation` (or `@saucebase/react/...`) in the module's `setup()`. `resolveIcon()` returns `undefined`, with no warning, for a name nobody registered.

## Aliases
The Vite preset in `saucebase/core` defines `@saucebase/core` and `@saucebase/<framework>` for core's `resources/js`, `@` for the app's frontend code (`resources/js`, or core's `stubs/stack/<framework>/js` in a contributor checkout), `@js` for `resources/js`, `@css` for `resources/css`, and `@modules` for `modules/`. Built chunks and assets from `modules/<name>/` are emitted under `assets/<name>/`.
