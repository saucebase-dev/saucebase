---
paths:
  - '**/database/migrations/**'
---

# Migrations

## New migrations use Laravel's timestamps
Create migrations with `php artisan make:migration <name>` (add `--module=<name>` for a module), keeping the generated timestamp. The timestamp sorts a new migration after everything already installed, so it runs on databases that have migrated before.

## Don't rename the `0000_00_00_` migrations
Migrations named `0000_00_00_NNNNNN_*` are the schema a module or the app shipped with, ordered by number. Renaming or editing one changes what an already-migrated database thinks has run; change the schema with a new timestamped migration instead.
