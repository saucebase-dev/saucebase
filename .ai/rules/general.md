---
paths:
  - '**'
---

# General

## Write only what the application needs to work
Before finishing, review every line you added and remove anything the app doesn't need to work: nicer messages, reminders, warnings, optimisations, guards for cases the framework or tooling already handles (e.g. Vite already fails on a missing import). If you are unsure whether something is needed, ask the user instead of assuming; never add it "just in case".
