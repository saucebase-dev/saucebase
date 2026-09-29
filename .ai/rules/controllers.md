---
paths:
  - '**/Http/Controllers/**'
---

# Controllers

## Validate request bodies with a FormRequest
Validate request input with a FormRequest class under `Http/Requests/` (app and modules), injected into the action. Don't use `$request->validate([...])` inline or a private `rules()` method on the controller.
