# Local Development Workflows & Daily Loop

This document guides developers on daily local workflows, tooling, and commands for the application.

---

## 🛠 Prerequisites & Local Setup

- **PHP 8.5 via Laravel Herd**: `C:\Users\USER\.config\herd\bin\php.bat`
- **Node.js**: v22+
- **Local URL**: `http://testvue.test`

---

## 🚀 Daily Development Commands

### 1. Boot Environment (`/dev`)
Run the interactive `/dev` workflow or execute manually:
```bash
# 1. Generate TypeScript Ziggy routes
php artisan ziggy:generate

# 2. Start Vite HMR server
npm run dev
```

### 2. Live Log Streaming
Stream incoming requests and exceptions using Laravel Pail:
```bash
php artisan pail
```

### 3. Running Continuous Tests
Run Pest tests in watch mode during development:
```bash
./vendor/bin/pest --watch
```

### 4. Code Quality & Verification
Run the verification suite before opening a commit or PR:
```bash
# Slash command:
/verify

# Or manual execution:
./vendor/bin/pint --test
npx vue-tsc --noEmit
npm run lint
npm run format:check
php artisan test
```

### 5. Local Database Reset (`/dev-seed`)
Reset local database and seed test accounts:
```bash
php artisan migrate:fresh --seed
```
Default test credentials:
- Email: `test@example.com`
- Password: `password`
