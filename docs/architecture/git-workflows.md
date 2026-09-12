# Git & CI/CD Workflows

This document outlines the version control workflows and automated continuous integration pipelines configured for the project.

---

## 🚀 Antigravity Slash Workflows

| Slash Command | Workflow File | Description |
| :--- | :--- | :--- |
| **`/feature-start`** | `.agents/workflows/feature-start.md` | Syncs `main`, creates a standard `feat/` or `fix/` branch, and initializes context in `docs/features/`. |
| **`/verify`** | `.agents/workflows/verify.md` | Runs full pre-flight verification: Pest PHP, Pint, ESLint, Prettier, and TypeScript (`vue-tsc`). |
| **`/git-commit`** | `.agents/workflows/git-commit.md` | Pre-commit formatting, secret leak detection, and conventional commit drafting. |
| **`/git-pr`** | `.agents/workflows/git-pr.md` | Runs verification, pushes branch upstream, and opens a formatted PR via GitHub CLI (`gh`). |

---

## ⚙️ GitHub Actions CI Pipeline (`.github/workflows/ci.yml`)

1. **`quality` Job**:
   - PHP Composer and Node.js dependency caching.
   - Code style check via Laravel Pint (`vendor/bin/pint --test`).
   - Formatting check via Prettier (`npm run format:check`).
   - Linting check via ESLint (`npm run lint`).
   - Strict TypeScript validation via `vue-tsc --noEmit`.
2. **`tests` Job**:
   - Executes the Pest PHP test suite against an isolated SQLite test database.
   - Requires `quality` to pass first.
