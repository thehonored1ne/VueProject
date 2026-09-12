---
name: inertia-feature-generator
description: >-
  Scaffolds an end-to-end Inertia.js feature in Laravel 12 + Vue 3 + TypeScript.
  Use this skill when creating a new full-stack feature, page, or CRUD resource,
  including migration, model, form request, controller, routes, Vue SFC, and Pest tests.
---

# Inertia Feature Generator

This skill guides the creation of cohesive, production-grade features in this Laravel 12 + Vue 3 (Inertia.js) application.

---

## Workflow Steps

Follow these steps sequentially to implement an end-to-end feature:

### 1. Database & Model
1. Generate the migration and model:
   ```bash
   php artisan make:model <ModelName> -m
   ```
2. In the migration file (`database/migrations/`):
   - Define columns with strict types and defaults.
   - Include foreign key constraints with cascade behaviors (`cascadeOnDelete()`).
   - Implement both `up()` and `down()` methods.
3. In the model (`app/Models/<ModelName>.php`):
   - Add fillable fields (`protected $fillable = [...]`).
   - Define typed relationships (`BelongsTo`, `HasMany`, etc.).
   - Define attribute casting using the `casts(): array` method.
4. Run migrations using Herd PHP:
   ```bash
   php artisan migrate
   ```

### 2. Validation (Form Request)
Generate a Form Request class when handling input submission:
```bash
php artisan make:request <StoreOrUpdate><ModelName>Request
```
- Define `authorize(): bool` (enforce policy or return `true` if handled via middleware).
- In `rules(): array`, return explicit validation rules with appropriate type casting and validation constraints.

### 3. Controller & Routing
1. Generate controller:
   ```bash
   php artisan make:controller <ModelName>Controller
   ```
2. Implement controller methods:
   - Type-hint request objects and return types (`Response` from `Inertia\Response`).
   - Return Inertia view:
     ```php
     return Inertia::render('<Feature>/<PageName>', [
         'items' => $items,
     ]);
     ```
3. Register routes in `routes/web.php` with appropriate middleware (e.g. `auth`, `verified`):
   ```php
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::resource('<feature-slug>', <ModelName>Controller::class);
   });
   ```

### 4. TypeScript Types
Add interfaces for model data and page props in `resources/js/types/`:
- If shared, update `resources/js/types/index.d.ts`.
- If feature-specific, create or export typed interfaces for the model attributes.

### 5. Vue 3 Inertia Page Component
Create the page component in `resources/js/pages/<Feature>/<PageName>.vue`:
- Use `<script setup lang="ts">`.
- Import layout: `import AppLayout from '@/layouts/AppLayout.vue';`.
- Import `Head` from `@inertiajs/vue3`.
- Define typed breadcrumbs (`BreadcrumbItem[]`).
- Define page props using `defineProps<{ ... }>()`.
- Use Lucide icons from `lucide-vue-next`.
- Use Tailwind CSS utility classes and project components.

### 6. Pest PHP Feature Test
Create a feature test in `tests/Feature/<Feature>Test.php`:
```bash
php artisan make:test <Feature>Test --pest
```
Write test cases covering:
1. Guest redirection to login: `->assertRedirect('/login')`.
2. Authenticated user access: `actingAs($user)->get(...)` asserting status `200` and Inertia component:
   ```php
   $response->assertInertia(fn (AssertableInertia $page) => $page
       ->component('<Feature>/<PageName>')
       ->has('items')
   );
   ```
3. Form validation failure scenarios.
4. Successful store/update actions with database assertion (`assertDatabaseHas`).

### 7. Verification & Code Quality
Run all required linters, tests, and formatting checks:
1. Backend tests:
   ```bash
   php artisan test --filter=<Feature>Test
   ```
2. PHP code formatting:
   ```bash
   ./vendor/bin/pint
   ```
3. Frontend lint and format:
   ```bash
   npm run lint
   npm run format
   ```
4. TypeScript check:
   ```bash
   npx vue-tsc --noEmit
   ```
