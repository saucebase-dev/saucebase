# Contributing to Saucebase

This repository is the contributor workspace: a Laravel app wired to develop
Saucebase itself. Most of the product lives in other packages:

- [`saucebase/core`](https://github.com/saucebase-dev/core): the framework, the
  Vue and React UI (`stubs/stack/`), and the Vite preset.
- Modules such as [`saucebase/auth`](https://github.com/saucebase-dev/auth), one
  repository each, installed into `modules/`.
- [`saucebase/installer`](https://github.com/saucebase-dev/installer): the
  `saucebase` CLI.

Fix a bug where its code lives, and open the pull request there.

## Requirements

- PHP 8.4+ and Composer
- Node 22.12+ and npm 10.5+
- The `saucebase` CLI: `composer global require saucebase/installer`

## Set up

```bash
git clone https://github.com/saucebase-dev/saucebase.git
cd saucebase
composer install
saucebase stack vue --dev          # or: react. Also runs npm install.
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed         # the seeder creates the roles
php artisan boost:install          # before adding modules, see below
composer dev                       # http://127.0.0.1:8000
```

`saucebase stack <vue|react> --dev` runs the UI straight from
`vendor/saucebase/core`. It writes small entry files in `resources/js/` that
point at core, and marks them skip-worktree so they don't show up in
`git status`.

`.env.example` uses SQLite, so nothing else is needed. With MySQL or PostgreSQL,
set the `DB_*` variables and create the database before migrating.

### Laravel Herd

Herd serves the folder name as the domain, so clone into a folder named after
the site you want, for example `saucebase-dev` for `saucebase-dev.test`. Then
set the URL in `.env` and enable HTTPS:

```dotenv
APP_URL=https://saucebase-dev.test
```

```bash
herd secure saucebase-dev
npm run dev                        # Herd serves PHP; Vite serves assets
```

If several local sites share one Redis, give each its own `REDIS_PREFIX`.

### Signing in

The app has no sign-in of its own. The home page works out of the box, but
`/dashboard` and other signed-in pages need a login route. At the moment, the
only compatible way to sign in is the auth module (see
[Working on a module](#working-on-a-module)). Install it when your change needs
signed-in pages. Its pages are under `/auth/` (`/auth/login`, `/auth/register`).
To open the admin panel at `/admin`, register, then run
`php artisan auth:make-admin you@example.com`. This needs the roles from
`migrate --seed`.

## Working on core

Core comes from Packagist by default. To change it, clone it next to this
repository and link it as a Composer path repository:

```bash
git clone https://github.com/saucebase-dev/core.git ../core
composer config repositories.core '{"type":"path","url":"../core","options":{"symlink":true}}'
composer update saucebase/core
```

`vendor/saucebase/core` is now a symlink, so edits to core show up straight
away. Do not commit the `composer.json` and `composer.lock` changes. To go back
to the released version, run `composer config --unset repositories.core` and
`composer update saucebase/core`.

The app's UI lives in core, under `stubs/stack/vue/js/` and
`stubs/stack/react/js/`. Edit it there, never the generated entry files in
`resources/js/` or the generated TypeScript declarations.

The CSS is the exception. It also lives in core (`stubs/stack/css/`), but its
files find modules and packages by relative path, so `saucebase stack` copies
them into `resources/css/`, which git ignores here. Edit that copy to see changes
live, including a theme set with **Set as default**, then run
`saucebase stack --sync` to copy it back and commit in core. `stack --reset`
refuses to continue while the copy has edits core doesn't.

Shared frontend changes must work with both stacks, and a module's Vue and
React trees must stay in step. To switch stacks, or to regenerate the entry
files after updating the CLI:

```bash
saucebase stack --reset
saucebase stack react --dev
```

When core is symlinked, `npm run dev` and `npm run build` print "Could not
resolve ... treating it as an external dependency" warnings for the build
plugins. They are harmless: Vite follows the symlink to load its config, then
finds the plugins in the app.

## Working on a module

Clone the module into `modules/`, then follow the install steps in its README
(for example [auth](https://github.com/saucebase-dev/auth#install)). Each module
has its own steps, such as applying a patch or seeding. Its
`composer require` resolves to your clone through the `modules/*` path
repository in `composer.json`:

```bash
git clone https://github.com/saucebase-dev/auth.git modules/auth
# then the module's install steps, e.g. composer require saucebase/auth
```

Like the core link, these changes (`composer.json`, `composer.lock`, applied
patches) are local. Do not commit them.

On a user's install, the module installer keeps only the selected stack and
flattens it into `resources/js/`, so documentation shipped with a module (its
`README.md`, `CLAUDE.md` and `resources/boost/`) must describe that installed
layout, not contributor mode.

## Development

```bash
composer dev                         # server, queue, logs and Vite
npm run dev                          # Vite only (use this with Herd)
npm run build                        # production client and SSR builds
```

`php artisan boost:install` generates your local `AGENTS.md`, `CLAUDE.md` and
`boost.json` (see [Documentation](#documentation)). Run it before adding a
module: every `composer require` and `composer update` runs `boost:update`,
which fails until Boost is installed.

## Conventions

Follow Laravel's conventions unless a recorded decision says otherwise. Where
Laravel names or places something, do the same: exceptions end in `Exception`
(`PurgeFailedException`), console commands in `Command`, form requests in
`Request`. A deliberate departure is written down here or in the relevant skill,
with the reason.

## Module conventions

Create a module from the maintained recipe:

```bash
php artisan saucebase:recipe Example basic
composer update <vendor>/example
npm run build
```

Every module has a `package.json`, where it declares any npm package the stack
manifest doesn't list (see `saucebase-module-development`). The app's npm
workspaces install them: after adding a module, run `npm install` so
`package-lock.json` lists it, or `npm ci` refuses the lockfile.

Module directories are lowercase, while PHP namespaces are TitleCase. Main
module providers extend `Saucebase\Core\Providers\ModuleServiceProvider`;
`$name` and `$nameLower` properties are obsolete because the base provider
resolves the module name through InterNACHI's `ModuleRegistry`.

A shipped module's migrations are named `1000_00_00_NNNNNN_<action>_table.php`,
numbered in dependency order: a module installs its whole schema at once, so
only the order matters. The prefix sorts them after Laravel's own migrations and
`saucebase/core`'s (both `0001_01_01_`), so the `users` table and core's tables
exist first, and before any real-dated migration. A module migration that has to
run after those, because it needs another module's table or changes a core
table, uses `1000_00_01_`. After a release, change the schema with a new timestamped migration,
because renaming a shipped one breaks databases that already ran it.

Installers choose their own database, so shipped migrations and queries must
work on MySQL, PostgreSQL and SQLite. MySQL indexes a foreign key column on its
own; PostgreSQL and SQLite do not. Give every foreign key column that queries
filter or join on an index of its own (or lead a composite index with it)
rather than relying on `constrained()`.

Tables for test-only models go in the module's `tests/Support/migrations/`; core
loads them only while tests run. Never create a table inside a test: on MySQL the
DDL commits the test's transaction.

An action (`src/Actions/`) does one thing and has exactly one public method,
`handle()`. Helpers it shares with other callers belong elsewhere — on the model,
or with the module's other utilities — not as extra public methods on the action.

Do not bypass the Vite preset in `saucebase/core` (`resources/js`). It discovers
module assets, translations, and Playwright projects. Module patterns are maintained in `saucebase/core`'s
`resources/boost/` (guideline plus the `saucebase-module-development` and
`saucebase-filament-development` skills), so they update with core.

A module ships its agent context in `resources/boost/`: a short
`guidelines/core.md` that is always loaded, and a
`skills/saucebase-<module>-development/SKILL.md` with the full detail. The
module's `CLAUDE.md` only imports that skill, and its `AGENTS.md` only points to
it. `composer boost:update` runs
`php artisan modules:boost` first, which links each module into the vendor path
Boost scans and registers it in `boost.json`, so modules never need listing by
hand. It registers `saucebase/core` the same way; core is a regular Composer
package, so it needs no link.

## Tests and quality checks

Run the smallest relevant checks while developing, then broaden coverage before
submitting:

```bash
php artisan test
php -d memory_limit=2048M artisan test --testsuite=Modules
composer analyse                      # Larastan/PHPStan level 5
composer lint                         # Laravel Pint
npm run lint                          # ESLint, fixes files in place
npm run format:check
npm run test:e2e                      # first run: npx playwright install
npm run build
```

Tests run on SQLite. Before changing migrations or raw queries, also run them on
MySQL and PostgreSQL. Point them at an empty database of their own, never your
app's: `RefreshDatabase` empties whatever it runs against.

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=testing DB_USERNAME=testing DB_PASSWORD=testing \
    php -d memory_limit=2048M artisan test modules/tenancy/tests
```

Use `DB_CONNECTION=pgsql` (port 5432) for PostgreSQL.

Some npm commands are unavailable until a frontend stack is selected. User-facing
work should include feature or E2E coverage; complex isolated logic should
include unit coverage. E2E tests must select stable `data-testid` attributes,
not translated text.

## Documentation

Documentation changes must be checked against implementation and manifests:

- `README.md` owns the public overview, installation links, and supported stack
  lines.
- `CONTRIBUTING.md` owns contributor setup and verification workflows.
- `.ai/guidelines/` owns always-loaded agent conventions.
- `.ai/skills/` owns task-specific agent workflows for the app.
- `.ai/rules/` owns path-scoped rules, listed in `.ai/rules/index.md`.
- `saucebase/core`'s `resources/boost/` owns module and Filament conventions.
- The root `AGENTS.md`, `CLAUDE.md`, and `boost.json` are local Laravel Boost outputs
  and are not tracked. `boost:install` also rewrites the tracked `.mcp.json`;
  don't commit that change.

Avoid repeating version tables across these files. When a dependency changes,
update its manifest first and adjust only documentation that describes the
affected supported version line.

Change agent instructions in `.ai/`, then regenerate your local outputs:

```bash
composer boost:update
```

Never edit the `<laravel-boost-guidelines>` blocks in `AGENTS.md` or
`CLAUDE.md` directly — they are overwritten on every regeneration, and they
are not tracked, so the edit reaches nobody. `.ai/` is the only source.

Installed modules and core are picked up by `composer boost:update` automatically. To
pick up guidelines newly shipped by any other package, run the Artisan command
directly. Composer sets `COMPOSER_DEV_MODE`, which suppresses the discovery
prompt, so `composer boost:update` cannot add third-party packages:

```bash
php artisan boost:update
```

## Commits and pull requests

Use a lowercase, single-line Conventional Commit:

```text
type(scope): subject
```

The scope is optional. Allowed types are `feat`, `fix`, `docs`, `style`,
`refactor`, `perf`, `test`, `chore`, `ci`, `build`, and `revert`. Keep the
header at or below 150 characters and do not add a body or footer. See
`.github/COMMIT_CONVENTION.md` for examples.

Pull requests should explain the problem and solution, identify affected stacks
or modules, and list the checks run. Include screenshots for visible UI changes
and keep unrelated changes out of the branch.
