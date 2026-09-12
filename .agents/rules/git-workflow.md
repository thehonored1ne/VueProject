# Git Branching & Feature Workflow Rules

This document establishes the Git branching, commit hygiene, Pull Request (PR), and merge strategy standards for the project.

---

## 1. Branching Strategy

### Base Branch
- All feature and fix branches are cut from `main` (or the default production branch).
- Direct commits or pushes to `main` are prohibited.

### Branch Naming Conventions
Use lowercase, hyphen-separated branch names prefixed with the appropriate category:

| Type | Format | Example |
| :--- | :--- | :--- |
| **Feature** | `feat/<short-description>` | `feat/user-settings-page`, `feat/invoice-pdf-export` |
| **Bug Fix** | `fix/<short-description>` | `fix/db-ssl-deprecation`, `fix/login-redirect-loop` |
| **Refactor** | `refactor/<short-description>` | `refactor/order-actions`, `refactor/composable-state` |
| **Performance** | `perf/<short-description>` | `perf/cursor-paginate-orders` |
| **Chore / Config** | `chore/<short-description>` | `chore/update-dependencies`, `chore/pint-rules` |

---

## 2. Commit Hygiene & Standards

### Atomic Commits
- Every commit must represent **one logical, bisectable change**.
- Do not combine unrelated features, bug fixes, and reformatting into a single massive commit.

### Conventional Commit Format
All commit messages must follow the Conventional Commits specification:
```
<type>(<optional-scope>): <concise-description-in-imperative-mood>

[optional body explaining motivation and context]

[optional footer, e.g. Closes #123]
```

- **Types**: `feat`, `fix`, `refactor`, `style`, `test`, `perf`, `chore`, `docs`.
- **Description**: Lowercase, imperative mood (e.g. `feat(auth): add email verification check`, NOT `added verification`).
- **No Secrets**: Never stage `.env`, private keys, credentials, or debug logs.

---

## 3. Pre-Commit Quality Gate

Before staging or committing any code:
1. Run backend tests: `php artisan test` (must pass with 0 failures).
2. Run formatters: `./vendor/bin/pint` and `npm run format`.
3. Check linters: `npm run lint` and `npx vue-tsc --noEmit`.

---

## 4. Pull Request (PR) Lifecycle

### Pull Request Requirements
1. **Descriptive Title**: Matches conventional commit syntax (e.g. `feat(orders): implement single-action order processing`).
2. **Context & Rationale**: Briefly explain *why* this change is needed and *what* approach was chosen.
3. **Proof of Verification**: Include a structured summary of passing tests and static checks.
4. **Visual Proof (UI changes)**: Embed screenshots or recordings for any new or modified Vue views.

---

## 5. Merge Strategy

- **Squash and Merge**: Preferred for feature branches to keep the `main` branch history clean, atomic, and bisectable.
- **Rebase & Delete**: Always delete the feature branch after merging to keep the repository clean.
