# Testing Saucebase

How to run and write the tests for the app, core and the modules: PHPUnit,
Vitest, Playwright end-to-end tests and Infection mutation tests.

Set up the repository first, as described in [CONTRIBUTING.md](CONTRIBUTING.md).

## Rules

> [!WARNING]
> Run PHP tests on the host, **never inside a Docker container**. Compose sets
> `DB_CONNECTION` and the other database variables on the container, and those
> override `phpunit.xml`. The tests would then run against your app's database,
> and `RefreshDatabase` wipes it.

- PHPUnit runs on SQLite in memory (`phpunit.xml`). It never needs your app's
  database.
- End-to-end tests run on their own server and SQLite database (`.env.e2e`), so
  they never touch your app's data. See [End-to-end tests](#end-to-end-tests-playwright).
- Pick the test for the change:

| Change                                                                      | Test                                                |
| --------------------------------------------------------------------------- | --------------------------------------------------- |
| Isolated logic: a calculation, a mapper, a value object                     | PHPUnit unit test, or Vitest for code under `lib/`  |
| A route, controller, policy, job, webhook or anything touching the database | PHPUnit feature test                                |
| A user-facing flow across pages                                             | Playwright, plus a feature test for the server side |
| Whether existing tests would notice a bug                                   | Infection                                           |

## PHPUnit

```bash
php artisan test                                              # app and modules
php -d memory_limit=2048M artisan test --testsuite=Modules    # modules only
php artisan test --testsuite=Modules --filter='^Modules\\Auth\\Tests'   # one module
php artisan test modules/auth/tests/Feature/LoginTest.php     # one file
```

A module has no PHPUnit config of its own. Its tests run inside the app, in the
`Modules` suite, and its namespace selects them.

Core has its own suite, run from the core checkout (see
[Working on core](CONTRIBUTING.md#working-on-core)):

```bash
cd ../core
vendor/bin/phpunit
```

### Other databases

Before changing migrations or raw queries, also run the tests on MySQL and
PostgreSQL. Point them at an empty database of their own, never your app's:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=testing DB_USERNAME=testing DB_PASSWORD=testing \
    php -d memory_limit=2048M artisan test modules/billing/tests
```

Use `DB_CONNECTION=pgsql` (port 5432) for PostgreSQL.

### Writing tests

- Assert what the code produces, not only that it ran. `assertOk()` alone
  passes for a page that renders the wrong data; assert the props, the stored
  rows or the sent payload.
- A test whose input matches the default proves nothing about the input. A
  redirect test that sends `/dashboard` passes even when the redirect is ignored,
  because `/dashboard` is also where users land without one.
- A test that writes a file must write it somewhere temporary and clean up in
  `finally`.

## Vitest

For TypeScript under `lib/`:

```bash
npm run test:unit
```

## End-to-end tests (Playwright)

The suite runs on its own server and database, from `.env.e2e`: a copy of `.env`
with `APP_ENV=e2e`, `APP_URL=http://localhost:8100` and SQLite
`database/e2e.sqlite`. Playwright starts that server itself (`laravelServer()` in
`playwright.config.ts`) and migrates its database, so your app and its data stay
untouched, however you serve them.

```bash
php artisan playwright:env              # first run only: writes .env.e2e from .env
npx playwright install                 # first run only
npm run test:e2e                       # everything
npx playwright test --project="@auth*" # one module
npm run test:e2e:ui                    # pick and watch tests
```

- Outside CI, the assets are rebuilt before the run.
- Each module ships its specs in `modules/<module>/tests/e2e`, registered as a
  `@<module>` project.
- Select elements by `data-testid`, never by translated text.
- An uncaught page error fails the test. A spec that expects one allows it with
  `test.use({ allowedPageErrors: [/pattern/] })`.

## Mutation testing (Infection)

Coverage says a line ran. Mutation testing says whether a test would notice if it
were wrong: [Infection](https://infection.github.io) makes small changes to the
code (a `>` becomes `>=`, a call is removed, `true` becomes `false`) and runs the
tests against each one. A change no test catches is a gap.

It is optional and nothing in the project depends on it. Use it to check the
tests around code you change.

### Setup

Infection needs a coverage driver. Herd's PHP has none, so install PHP with
[Homebrew](https://brew.sh) next to Herd, and add [PCOV](https://github.com/krakjoe/pcov):

```bash
brew install php pcre2
CPPFLAGS="-I$(brew --prefix pcre2)/include" pecl install pcov
/opt/homebrew/bin/php -m | grep pcov    # prints "pcov"
```

`CPPFLAGS` is there because the PCOV build cannot find the `pcre2` headers
without it.

Get Infection as a PHAR, outside the project, so nothing is added to
`composer.json`:

```bash
mkdir -p ~/.local/bin
curl -fsSL -o ~/.local/bin/infection.phar \
    https://github.com/infection/infection/releases/latest/download/infection.phar
```

Infection starts PHPUnit through `vendor/bin/phpunit`, which runs whichever
`php` comes first on your `PATH`. Put Homebrew's first for every run, or PHPUnit
runs under Herd's PHP and reports no coverage driver:

```bash
export PATH="/opt/homebrew/bin:$PATH"
```

### Configuration

Create `infection.json5` in the folder you test: the core checkout, or
`modules/<module>`. Keep it out of that repository's git:

```bash
echo infection.json5 >> .git/info/exclude                # in the core checkout
echo infection.json5 >> modules/auth/.git/info/exclude   # for a module, from the app root
```

For core, in the core checkout:

```json5
{
    $schema: 'https://raw.githubusercontent.com/infection/infection/master/resources/schema.json',
    source: { directories: ['src'], excludes: ['Filament'] },
    timeout: 30,
    logs: {
        text: '/tmp/infection-core/infection.log',
        html: '/tmp/infection-core/infection.html',
    },
    mutators: { '@default': true, ProtectedVisibility: false },
}
```

For a module, in the module's folder (`modules/<module>`), pointing PHPUnit at
the app:

```json5
{
    $schema: 'https://raw.githubusercontent.com/infection/infection/master/resources/schema.json',
    source: { directories: ['src'], excludes: ['Filament'] },
    phpUnit: { configDir: '../..' },
    timeout: 30,
    logs: {
        text: '/tmp/infection-auth/infection.log',
        html: '/tmp/infection-auth/infection.html',
    },
    mutators: { '@default': true, ProtectedVisibility: false },
}
```

- `Filament` is excluded because admin forms and tables are declarations: their
  mutants are layout values no test should pin down.
- `timeout` is raised from the default 10 seconds. With 10, slow mutants are
  skipped instead of tested, and the score looks better than it is.
- `ProtectedVisibility` (making a `protected` method `private`) only finds
  methods no subclass overrides, which is not a test gap.

Paths in the config are relative to the config's own folder.

### Running

Core, from the core checkout:

```bash
php -d pcov.enabled=1 ~/.local/bin/infection.phar --threads=max
```

A module, **from the app root**: Infection looks for `vendor/bin/phpunit` in the
folder it runs from, and only the app has one. Point it at the module's config,
and select the module's tests by namespace, since they run in the app's
`Modules` suite:

```bash
php -d pcov.enabled=1 -d memory_limit=2048M ~/.local/bin/infection.phar --threads=max \
    --configuration=modules/auth/infection.json5 \
    --test-framework-options="--testsuite=Modules --filter=^Modules.Auth.Tests."
```

The filter writes `.` where the namespace has `\`. Infection removes
backslashes from these options before passing them on, so a filter written with
`\\` reaches PHPUnit broken and matches no tests.

Narrow a run while you work by naming files, relative to the folder you test:

```bash
# core: one file
php -d pcov.enabled=1 ~/.local/bin/infection.phar --threads=max src/Navigation/Navigation.php

# core: only the lines your branch changed
php -d pcov.enabled=1 ~/.local/bin/infection.phar --threads=max --git-diff-lines --git-diff-base=origin/main

# a module: the files your branch changed, from the app root
php -d pcov.enabled=1 -d memory_limit=2048M ~/.local/bin/infection.phar --threads=max \
    --configuration=modules/auth/infection.json5 \
    --test-framework-options="--testsuite=Modules --filter=^Modules.Auth.Tests." \
    $(git -C modules/auth diff --name-only origin/main -- src)
```

`--git-diff-lines` does not work for a module: Infection runs `git` in the
folder it runs from, which is the app's repository, not the module's.

A full run takes seconds for a small module and up to an hour for core or
billing. Naming a few files takes a minute or less.

### After every run

Mutants run your code changed, and a changed path or a skipped clean-up step
writes where it should not. Killed mutants do this too, because they run before
they fail. Check both repositories when a run ends:

```bash
git status                   # the folder you tested
git -C ../.. status          # for a module: the app as well
```

Delete what the run created and `git checkout` what it changed. Seen so far:
a scaffolded module left in the core folder by the recipe command tests, a test
theme written over `modules/themes/resources/themes/default.json`, and images in
`storage/media-library/temp`.

### Reading the report

- **Covered Code MSI** is the share of mutants the tests caught, among code the
  tests run. It is the number to watch.
- **Escaped** (not detected) mutants are the gaps. Open the HTML report, or the
  text log, to see each one as a diff.
- Some escaped mutants change nothing a caller can see. Leave these alone:
    - text in log calls,
    - a value that is cast again anyway (`(string)` on what is already a string),
    - a random token length one character shorter or longer,
    - a check the code already made earlier.
- The rest are tests worth writing. Write the test, then run Infection on that
  file again to confirm the mutant is killed.

## Static checks

```bash
composer analyse        # Larastan / PHPStan level 5
composer lint           # Laravel Pint
npm run lint            # ESLint, fixes files in place
npm run format:check    # Prettier
npm run build
```

Some npm commands are unavailable until a frontend stack is selected.
