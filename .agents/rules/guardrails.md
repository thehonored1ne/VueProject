---
trigger: always_on
---

# Project Guardrails & Safety Constraints

This document defines the **non-negotiable safety rules, structural guardrails, and behavioral boundaries** for operating within this codebase. Violating these guardrails is strictly prohibited.

---

## 🛡️ 1. Data Safety & Command Execution Guardrails

- **NO Silent Database Destruction**:
  - NEVER execute `migrate:fresh`, `migrate:reset`, or `db:wipe` without explicit, unambiguous confirmation from the user.
  - Test database modifications must strictly run in isolated test environments (e.g. SQLite in-memory or dedicated test database), never against local development databases without consent.
- **NO Unrecoverable Git or File Deletions**:
  - NEVER run destructive Git operations like `git reset --hard`, `git clean -fd`, or force pushes (`git push --force`).
  - NEVER run bulk directory removal commands like `rm -rf`, `rmdir /s`, or `Remove-Item -Recurse -Force` on workspace directories.
- **NO Secret Exposure**:
  - NEVER write, display, or commit plaintext secrets, API keys, passwords, or credentials into repository files.
  - All sensitive parameters must remain exclusively in `.env` or `.env.local` and accessed via `config('services...')` or `env('...')`.
  - Ensure `.env` is never added to Git staging.

---

## 🏗️ 2. Architectural & Code Quality Guardrails

- **NO Fat Controllers**:
  - NEVER place multi-step business logic, complex data transformations, or `DB::transaction()` inside HTTP Controllers.
  - Controllers must only validate inputs (via Form Requests), invoke single-purpose **Action** classes (`app/Actions`), and return an Inertia or JSON response.
- **NO Unvalidated User Input**:
  - NEVER pass raw `$request->all()` or `$request->input()` directly to Eloquent models or database calls.
  - Every mutating endpoint must use a dedicated **Form Request** (`app/Http/Requests`) or strongly-typed **DTO** (`app/DTOs`).
- **NO Raw SQL Concatenation (SQL Injection Prevention)**:
  - NEVER concatenate untrusted variables or input strings into raw SQL queries (`DB::raw("... $var ...")`, `whereRaw`).
  - Always use query builder parameter bindings (`whereRaw('status = ?', [$status])`).
- **NO Untyped Code**:
  - **PHP**: Always add `declare(strict_types=1);` to new files. Disallow untyped parameters and return values. Avoid `mixed` where concrete types are identifiable.
  - **TypeScript**: Strictly forbid `any`. Define explicit types or interfaces in `resources/js/types/` for all props, emits, and Inertia page payloads.
- **NO Speculative Over-Engineering or Premature Abstraction**:
  - NEVER create unnecessary abstract classes, interfaces, generic factories, or multi-tiered repository layers when a single concrete class or direct Eloquent query is sufficient (YAGNI principle).
  - Write straightforward, readable code that solves the immediate requirement cleanly.
  - Avoid designing for hypothetical future use cases that add unnecessary indirection or complexity.
- **NO Monolithic "God Files" (Enforce Modularity & SRP)**:
  - NEVER place multiple responsibilities, unrelated concerns, or multiple class declarations into a single file.
  - **Backend**: Exactly one class per file. Every class must adhere strictly to the Single Responsibility Principle (SRP) with one clear reason to change.
  - **Frontend**: Do not build giant monolithic Vue components. Decompose large views into modular presentational sub-components (`components/`) and extract stateful logic into composables (`composables/`).

---

## ⚡ 3. Performance & Database Guardrails

- **NO Unbounded Queries**:
  - NEVER execute unrestricted `Model::all()` or high-volume queries in controllers, exports, or batch jobs.
  - Always use `paginate()`, `simplePaginate()`, or `cursorPaginate()`.
  - Use `chunkById()` or `lazyById()` for batch background mutations.
- **NO N+1 Query Introductions**:
  - NEVER iterate over relationships in templates, controllers, or resource transformers without explicit eager loading (`with()`).
  - Use `withCount()` or `withExists()` for aggregates; never load entire collections just to check counts or existence.
- **Maintain PHP 8.2+ Backward Compatibility**:
  - Although running PHP 8.5 via Laravel Herd locally, NEVER use PHP 8.5-exclusive features or constants (such as `Pdo\Mysql::ATTR_SSL_CA`) without backward-compatible guards (`defined(...) ? ... : ...`).

---

## 🎨 4. Frontend & UI/UX Guardrails

- **NO "AI Slop" Aesthetics**:
  - NEVER generate gratuitous purple/cyan radial gradients, floating blur blobs, or card-in-card nested padding waste.
  - Maintain high information density, intentional typography, and grounded neutral surfaces.
- **NO Inaccessible Interfaces**:
  - NEVER use placeholder text as a substitute for accessible `<label>` elements.
  - All interactive elements must have visible `:focus-visible` outline rings, disabled states, and keyboard navigability.
  - NEVER leave empty lists or failed requests blank; always render intentional empty states and skeleton loading states.
  - AVOID Emojis

---

## 🚦 5. Pre-Completion Verification & Delivery Gate

- **NO Delivery Without Prior Testing**:
  - NEVER conclude a task without executing tests first (`php artisan test` or `./vendor/bin/pest`).
  - If any error or test failure occurs during testing, it MUST be diagnosed and resolved immediately before presenting the task as complete.
- **Mandatory Quality Checks**:
  1. **PHP Tests**: `php artisan test` (Pest PHP suite passes with 0 failures).
  2. **PHP Styling**: `./vendor/bin/pint --test` (No code styling violations).
  3. **Frontend Lint**: `npm run lint` (ESLint 0 errors).
  4. **TypeScript Check**: `npx vue-tsc --noEmit` (0 type errors).
  5. **Formatting**: `npm run format:check` (Prettier clean).
- **Mandatory Completion Proof**:
  - The final completion message MUST explicitly demonstrate:
    1. What was built
    2. What was changed
    3. Proof of passing tests presented as a **clean, structured summary** (test count, assertions, 0 failures, duration). **NEVER dump raw terminal output**.
