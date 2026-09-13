---
description: Run the full-stack verification suite (Pest PHP, Pint, ESLint, Prettier, and TypeScript) with automated repair.
---

# Full-Stack Verification Workflow (`/verify`)

This workflow executes all quality, safety, and testing gates defined in the project guardrails to verify code correctness before deployment or task completion.

### ⚡ Quick One-Command Verification
Run the unified verification suite that executes Pint, Pest, ESLint, and Prettier in sequence with a clean summary:
```bash
php artisan app:verify
```
To automatically repair styling issues:
```bash
php artisan app:verify --fix
```

---

## Detailed Step-by-Step Gates

### Step 1: Backend Code Styling (Laravel Pint)
Run the Pint style checker:
```bash
./vendor/bin/pint --test
```
- **If violations occur**: Automatically format files with `./vendor/bin/pint` and re-verify.

### Step 2: Pest PHP Test Suite
Execute the backend test suite:
```bash
php artisan test
```
- Verify that **all test suites pass with 0 failures**.
- If any test fails, inspect the stack trace, locate the root cause in the relevant Action, Model, or Controller, and resolve the failure.

### Step 3: TypeScript Type Checking (`vue-tsc`)
Run the Vue TypeScript compiler in check-only mode:
```bash
npx vue-tsc --noEmit
```
- Ensure **0 TypeScript errors** exist across all `.ts` and `.vue` files.
- Forbid untyped `any` or missing prop definitions.

### Step 4: Frontend Linting (ESLint)
Run ESLint across the codebase:
```bash
npm run lint
```
- Address any unused imports, syntax issues, or Vue template warnings.

### Step 5: Frontend Code Formatting (Prettier)
Check code formatting across `resources/`:
```bash
npm run format:check
```
- **If formatting issues are found**: Automatically run `npm run format` to bring code into compliance.

### Step 6: Verification Summary Report
Output a clean, high-craft summary table:

```
┌──────────────────────────────┬────────────┐
│ Check                        │ Status     │
├──────────────────────────────┼────────────┤
│ 1. Laravel Pint (PHP Style)  │ ✅ PASSED  │
│ 2. Pest PHP (Unit & Feature) │ ✅ PASSED  │
│ 3. Vue-TSC (TypeScript)      │ ✅ PASSED  │
│ 4. ESLint (Frontend Lint)    │ ✅ PASSED  │
│ 5. Prettier (Code Format)    │ ✅ PASSED  │
└──────────────────────────────┴────────────┘
```
