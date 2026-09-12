---
name: security-audit
description: >-
  Performs an automated and structured security audit across this Laravel 12 and Vue 3 codebase.
  Use when reviewing code for security vulnerabilities, auditing authorization gates, checking
  for secret leaks, verifying SQL injection prevention, or scanning dependencies for CVEs.
---

# Security Audit & Hardening Skill

This skill guides a comprehensive security audit of this application to detect, prevent, and remediate security vulnerabilities across backend and frontend layers.

---

## Audit Workflow

Follow these audit steps systematically:

### Step 1: Secret & Credential Leak Audit
Verify that no secrets or sensitive files are exposed in the repository:
1. Check git status and staged files:
   ```bash
   git status -s
   ```
2. Scan for hardcoded keys, JWT tokens, or passwords in `config/` and `app/`:
   - Verify all secrets reference `env('...')` or `config('...')`.
   - Verify `.env` is listed in `.gitignore` and has not been committed.

### Step 2: Dependency Vulnerability Scan
Run automated security advisories across PHP and Node.js dependencies:
1. **PHP Composer Dependencies**:
   ```bash
   composer audit
   ```
2. **Node.js NPM Dependencies**:
   ```bash
   npm audit --audit-level=high
   ```
If any high or critical vulnerabilities exist, report them with recommended patch versions.

### Step 3: Authorization & IDOR/BOLA Review
Inspect all routes and controllers for proper access control:
1. **Route Middleware**: Check `routes/web.php` to ensure all mutating or user-specific routes are wrapped in `['auth', 'verified']`.
2. **Policy Enforcement**:
   - For every controller action accessing a specific model (`show`, `update`, `destroy`), verify that `$this->authorize('view', $model)` or `Gate::authorize(...)` is invoked.
   - For nested resources, verify scoped route bindings or model ownership checks (`$request->user()->id === $model->user_id`).
3. **No Unauthenticated Mutating Routes**: Ensure no POST/PUT/PATCH/DELETE endpoints lack auth and CSRF protection.

### Step 4: Mass Assignment & Injection Audit
1. **Eloquent Models**:
   - Scan `app/Models/` for `$guarded = []` (must be replaced with explicit `$fillable`).
   - Check that all models have `$hidden` configured for sensitive attributes (`password`, `remember_token`).
2. **SQL Parameterization**:
   - Search for raw SQL statements: `DB::raw`, `whereRaw`, `havingRaw`, `orderByRaw`.
   - Verify that all raw SQL queries use parameterized bindings (`whereRaw('status = ?', [$status])`) rather than string concatenation or interpolation.

### Step 5: Frontend XSS & Content Safety Audit
1. **`v-html` Usage**:
   - Search `resources/js/` for any instances of `v-html`.
   - Ensure `v-html` is never bound to user input or un-sanitized API response fields.
2. **External Links**:
   - Ensure all `<a target="_blank">` tags include `rel="noopener noreferrer"`.

### Step 6: Rate Limiting & Auth Hardening
1. Check that login, registration, and password reset endpoints have throttling applied (`throttle:5,1` or `throttle:6,1`).
2. Check that user passwords use the `'password' => 'hashed'` cast in `app/Models/User.php`.

---

## Audit Summary Report Format

Produce a standardized audit scorecard:

```
┌──────────────────────────────────────┬────────────┬────────────────────────┐
│ Security Check                       │ Status     │ Findings / Action      │
├──────────────────────────────────────┼────────────┼────────────────────────┤
│ 1. Secret & Key Leak Check           │ ✅ PASSED  │ No exposed secrets     │
│ 2. Composer Dependencies (PHP)       │ ✅ PASSED  │ 0 known CVEs           │
│ 3. NPM Dependencies (JS)             │ ✅ PASSED  │ 0 high/crit CVEs       │
│ 4. Authorization & IDOR Prevention   │ ✅ PASSED  │ Policies enforced      │
│ 5. Mass Assignment & SQL Injection   │ ✅ PASSED  │ Explicit fillable & ?  │
│ 6. Frontend XSS (v-html check)       │ ✅ PASSED  │ No unescaped v-html    │
│ 7. Rate Limiting & Auth Protections  │ ✅ PASSED  │ Throttling enabled     │
└──────────────────────────────────────┴────────────┴────────────────────────┘
```
