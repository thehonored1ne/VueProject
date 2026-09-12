---
description: Launch local development environment (Vite HMR dev server, Herd site check, Ziggy routes, and log monitoring).
---

# Local Dev Environment Launcher (`/dev`)

This workflow prepares and boots the local development environment for Laravel 12 + Vue 3 (Inertia.js) on Laravel Herd.

---

## Steps

### Step 1: Pre-Flight Environment Sanity Check
1. Verify `.env` file exists and has application key:
   ```bash
   php artisan key:generate --show
   ```
2. Verify SQLite database exists:
   - Ensure `database/database.sqlite` is present or create it:
     ```bash
     touch database/database.sqlite
     ```
3. Run pending migrations:
   ```bash
   php artisan migrate
   ```

### Step 2: Regenerate Ziggy Route Definitions
Ensure frontend TypeScript route helper (`ziggy-js`) is in sync with `routes/web.php`:
```bash
php artisan ziggy:generate
```

### Step 3: Launch Vite HMR Server
Start the Vite development server in daemon mode:
```bash
npm run dev
```
- Listens on `http://localhost:5173`.
- Hot Module Replacement (HMR) will automatically update Vue components without full page reloads.

### Step 4: Verify Local Herd Domain
Confirm your local Herd virtual host is serving the application:
- Default Herd URL: `http://testvue.test`
- Test connection:
  ```bash
  curl -I http://testvue.test
  ```

### Step 5: (Optional) Stream Real-Time Logs
To tail application exceptions and HTTP requests live in the console, run:
```bash
php artisan pail
```
