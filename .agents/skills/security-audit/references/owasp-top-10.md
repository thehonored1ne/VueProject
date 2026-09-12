# OWASP Top 10 Reference for Laravel & Vue

Guidance for recognizing, preventing, and fixing the most critical web application security risks in this stack.

---

## 1. Broken Access Control (A01:2021)
- **Vulnerability**: An attacker accesses another user's resources simply by changing the ID in the URL (`/orders/42` -> `/orders/43`).
- **Remediation**:
  ```php
  // In OrderController.php
  public function show(Order $order): Response
  {
      $this->authorize('view', $order); // Enforces OrderPolicy::view()

      return Inertia::render('Orders/Show', ['order' => $order]);
  }
  ```
  And in `OrderPolicy.php`:
  ```php
  public function view(User $user, Order $order): bool
  {
      return $user->id === $order->user_id;
  }
  ```

---

## 2. Cryptographic Failures (A02:2021)
- **Vulnerability**: Passwords hashed with weak algorithms or stored in plaintext, or unmasked sensitive tokens in API responses.
- **Remediation**:
  - Always use `hashed` cast in models:
    ```php
    protected function casts(): array {
        return ['password' => 'hashed'];
    }
    ```
  - Hide secrets from serialization:
    ```php
    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];
    ```

---

## 3. Injection (A03:2021)
- **Vulnerability**: User input directly interpolated into SQL strings.
- **Remediation**:
  ```php
  // ❌ Vulnerable:
  DB::select("SELECT * FROM users WHERE status = '$status'");

  // ✅ Secure:
  DB::select('SELECT * FROM users WHERE status = ?', [$status]);
  ```

---

## 4. Insecure Design & Mass Assignment (A04:2021)
- **Vulnerability**: Submitting unvalidated fields such as `is_admin=1` in registration or profile update forms.
- **Remediation**:
  - Form Requests with explicit rules:
    ```php
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ];
    }
    ```
  - In controller:
    ```php
    $user->update($request->validated()); // Only validated keys are passed
    ```

---

## 5. Security Misconfiguration (A05:2021)
- **Vulnerability**: `APP_DEBUG=true` in production leaking stack traces and environment variables.
- **Remediation**:
  - Ensure `.env` in production sets `APP_DEBUG=false` and `APP_ENV=production`.

---

## 6. Vulnerable and Outdated Components (A06:2021)
- **Remediation**:
  - Run `composer audit` and `npm audit` periodically.
  - Keep packages updated via `composer update --dry-run` and `npm outdated`.

---

## 7. Identification and Authentication Failures (A07:2021)
- **Remediation**:
  - Implement rate limiting on login:
    ```php
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->email.$request->ip());
    });
    ```

---

## 8. Cross-Site Scripting (XSS) (A03/Frontend)
- **Vulnerability**: Injecting malicious JavaScript via unescaped HTML.
- **Remediation**:
  - In Vue 3, always use double curly braces `{{ content }}`.
  - Never use `v-html` for dynamic user-generated comments or profiles.
