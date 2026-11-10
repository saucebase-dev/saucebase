---
paths:
  - '**/tests/e2e/**'
---

# E2E tests

## Select by test id
Select elements by `data-testid`, never by translated text, labels, or role names, which change with the locale. Item-specific ids use `{action}-${item.id}`.

## Assert behaviour, not copy or design
Customers translate, reword and restyle a starter kit; a test must still pass. Assert what happened: an element is shown or hidden, a URL, a count, a `data-state`, data the test created (a name, price, version). Never assert UI copy (labels, toasts, errors, empty states), CSS, sizes or positions. If a state has no hook, add a `data-testid` or `data-state` to the component instead of matching its words. The same holds in PHPUnit: assert data and state, not `assertSee` on a heading or sentence.

## Import the shared fixture
Import `test` and `expect` from `@e2e/fixtures`, not `@playwright/test`. The fixture provides `credentials` and `loginAs`, and fails the test on any uncaught page error; list errors a spec causes on purpose with `test.use({ allowedPageErrors: [...] })`.

## Don't test rate limits here
Playwright workers share one IP, so a lockout from one spec leaks into every other login. Cover throttling in PHPUnit.

## A module prepares its own users
When a module needs an E2E user in a certain state, define `Modules\<Name>\Tests\Support\E2eUser::prepare(User $user)`. Specs then don't need to know which other modules are installed.
