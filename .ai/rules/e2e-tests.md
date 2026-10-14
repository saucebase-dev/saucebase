---
paths:
  - '**/tests/e2e/**'
---

# E2E tests

## Select by test id
Select elements by `data-testid`, never by translated text, labels, or role names, which change with the locale. Item-specific ids use `{action}-${item.id}`.

## Import the shared fixture
Import `test` and `expect` from `@e2e/fixtures`, not `@playwright/test`. The fixture provides `credentials` and `loginAs`, and fails the test on any uncaught page error; list errors a spec causes on purpose with `test.use({ allowedPageErrors: [...] })`.

## Don't test rate limits here
Playwright workers share one IP, so a lockout from one spec leaks into every other login. Cover throttling in PHPUnit.

## A module prepares its own users
When a module needs an E2E user in a certain state, define `Modules\<Name>\Tests\Support\E2eUser::prepare(User $user)`. Specs then don't need to know which other modules are installed.
