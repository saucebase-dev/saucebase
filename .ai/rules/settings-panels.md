---
paths:
  - '**/src/Settings/**'
  - '**/pages/Settings*'
---

# Settings panels

## A panel renders inside the modal's shell
Render a bare `space-y-8` block that opens with a muted `p` description, and give it a `settings-<slug>-panel` test id. Don't wrap it in a `Card` or repeat its title: the modal already draws both, so the panel would show them twice.

