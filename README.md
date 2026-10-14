<p align="center">
  <br/>
  <strong>The AI-ready Laravel SaaS kit.</strong>
  <br/>
 Everything your SaaS needs, ready for your AI to build on<br/><br/>
</p>

<div align="center">

[![Tests](https://github.com/saucebase-dev/saucebase/actions/workflows/test.yml/badge.svg)](https://github.com/saucebase-dev/saucebase/actions/workflows/test.yml)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Vue 3.5](https://img.shields.io/badge/Vue-3.5-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org)
[![React 19](https://img.shields.io/badge/React-19-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

</div>

Billing, workspaces, a blog and an admin are already built and tested, on a Vue or React UI you own. Every module ships Laravel Boost guidelines and skills, so Claude Code, Cursor or any agent builds on them correctly from the first prompt.

## Install

```bash
curl -fsSL https://install.saucebase.dev | bash
```

It installs PHP and Composer if they are missing, then asks how to run the app (Docker, native PHP or Laravel Herd), Vue or React, and which modules to add.

Already have PHP and Composer?

```bash
composer global require saucebase/installer
saucebase new my-app
```

[Try the online demo](https://demo.saucebase.dev/).

### With an AI agent

Paste this into Claude Code, Cursor or any coding agent:

```text
Create a new Saucebase app called my-app. Install the CLI with
`composer global require saucebase/installer`, then run
`saucebase new my-app --driver=native --stack=vue --all-modules --no-interaction`.
When it finishes, run `npm install` and `npm run build` in my-app, and tell me
the URL to open.
```

This installs everything with defaults: native PHP, Vue and every module. To choose the environment, framework and modules yourself, run the interactive installer above instead.

## Contributing

This repository is the contributor workspace. To work on Saucebase itself, see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT.

---

The documentation is being updated for v3.
