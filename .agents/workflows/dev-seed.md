---
description: Controlled local development database reset, migration, and seeding with test credentials.
---

# Dev Database Refresh & Seeder (`/dev-seed`)

This workflow refreshes the local development database and seeds test accounts and dummy data.

> [!CAUTION]
> This command will reset local database tables. Strictly permitted **only** in local development (`APP_ENV=local`).

---

## Steps

### Step 1: Environment Safety Guard
Verify the application environment is strictly local before proceeding:
```bash
php artisan env
```
If `APP_ENV` is not `local`, abort immediately.

### Step 2: Refresh Database & Run Migrations
Run fresh migrations with Herd PHP:
```bash
php artisan migrate:fresh --seed
```

### Step 3: Display Default Local Credentials
Remind the developer of seeded default test credentials:
- **Test User**: `test@example.com`
- **Password**: `password`
- **URL**: `http://testvue.test/login`
