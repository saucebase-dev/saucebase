---
paths:
  - '**/database/migrations/**'
---

# Migrations

## New migrations use Laravel's timestamps
Create migrations with `php artisan make:migration <name>` (add `--module=<name>` for a module), keeping the generated timestamp. The timestamp sorts a new migration after everything already installed, so it runs on databases that have migrated before.

## Don't rename the `1000_00_` migrations
Migrations named `1000_00_00_NNNNNN_*` are the schema a module shipped with, ordered by number; `1000_00_01_` holds the ones that must run after those. The prefix sorts them after Laravel's and `saucebase/core`'s migrations (`0001_01_01_`) and before any real date. Renaming or editing one changes what an already-migrated database thinks has run; change the schema with a new timestamped migration instead.
