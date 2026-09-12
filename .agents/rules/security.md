# Application Security Rules & Guidelines

This document defines mandatory security standards and defensive programming practices for the application across backend (Laravel 12) and frontend (Vue 3 / Inertia).

---

## 1. Authentication & Authorization (Broken Access Control Prevention)

- **Enforce Policies & Gates**:
  - Every resource endpoint modifying or displaying tenant/user data **must** verify authorization using Laravel Policies (`$this->authorize(...)` or `Gate::authorize(...)`).
  - Never assume an endpoint is protected solely by an `auth` middleware.
- **Prevent Insecure Direct Object References (IDOR / BOLA)**:
  - NEVER trust client-supplied identifiers to determine ownership (e.g. do not accept `user_id` from `$request->all()`).
  - Always derive ownership from the authenticated session: `$request->user()->items()->create(...)`.
  - Use scoped route model binding for nested resources:
    ```php
    Route::get('/workspaces/{workspace}/projects/{project}', ...)->scopeBindings();
    ```

---

## 2. Mass Assignment & Injection Prevention

- **Strict Mass Assignment Protection**:
  - NEVER set `protected $guarded = [];` on Eloquent models.
  - Always specify explicit `protected $fillable = [...]` arrays.
  - NEVER pass unvalidated input to models (`Model::create($request->all())`).
  - Mutating operations must only consume `$request->validated()` from dedicated Form Requests or strongly typed DTOs.
- **SQL Injection Prevention**:
  - NEVER interpolate or concatenate dynamic variables into raw SQL fragments (`DB::raw("WHERE status = '$status'")`).
  - Always pass dynamic variables as parameterized bindings:
    ```php
    // ❌ FORBIDDEN:
    User::whereRaw("email = '$email'")->get();

    // ✅ MANDATORY:
    User::whereRaw('email = ?', [$email])->get();
    ```

---

## 3. Cross-Site Scripting (XSS) & Frontend Safety

- **Forbid Raw HTML Rendering**:
  - NEVER use `v-html` with user-supplied or untrusted input in Vue components.
  - Standard Vue template interpolations (`{{ dynamicText }}`) automatically escape HTML entities.
  - If rich-text HTML rendering is required, sanitize through an approved DOM sanitizer before rendering.
- **Link Target Safety**:
  - When rendering external links with `target="_blank"`, always include `rel="noopener noreferrer"` to prevent window opener hijacking.

---

## 4. Sensitive Data Handling & Cryptography

- **Credential Hashing & Masking**:
  - Passwords must always be hashed using Laravel's native hashing system with the `hashed` attribute cast:
    ```php
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    ```
  - Always mark sensitive fields as `$hidden` in Eloquent models to prevent accidental serialization into JSON or Inertia responses:
    ```php
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'api_token',
    ];
    ```
- **Logging Safety**:
  - Never log passwords, tokens, full credit card numbers, or authorization headers into application logs.

---

## 5. Rate Limiting & Denial of Service

- Protect all public, authentication, and expensive API endpoints with explicit rate limiting in `routes/web.php` or `routes/api.php`:
  ```php
  Route::middleware(['throttle:60,1'])->group(...);
  Route::post('/login', ...)->middleware('throttle:5,1');
  ```
