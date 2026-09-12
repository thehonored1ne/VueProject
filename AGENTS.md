# Project Rules & Guidelines (`testvue`)

This is a full-stack Laravel 12 application using Vue 3, Inertia.js, TypeScript, Tailwind CSS, and Pest PHP, running locally with Laravel Herd.

> [!IMPORTANT]
> All work must strictly adhere to the non-negotiable safety and architectural boundaries defined in [guardrails.md](.agents/rules/guardrails.md).

---

## 1. Environment & Runtimes

- **PHP Runtime**: The application is managed via Laravel Herd running PHP 8.5 (`C:\Users\USER\.config\herd\bin\php.bat`).
- **PHP Compatibility**: Maintain backward compatibility with PHP 8.2+. When using PHP 8.5+ features (such as `Pdo\Mysql::ATTR_SSL_CA`), provide safe fallback guards (`defined(...) ? ... : ...`).
- **Node.js**: Use modern Node.js and `npm` for frontend package management and building.

---

## 2. Clean Backend Architecture (Laravel 12)

- **Framework**: Laravel 12.x running PHP 8.5 on Herd (PHP 8.2+ backward-compatible).
- **Architectural Layers & Separation of Concerns**:
  - **HTTP Layer (Controllers & Requests)**:
    - Controllers must remain strictly **thin**. Their only responsibility is handling HTTP input, delegating to Action/Service classes, and returning an Inertia or JSON response.
    - **No business logic or raw database transactions inside controllers**.
    - Always encapsulate validation and authorization inside dedicated **Form Request** classes (`app/Http/Requests`).
  - **Domain Layer (Actions & Services - `app/Actions`)**:
    - Encapsulate distinct business operations into **Single-Action Classes** (e.g., `CreateOrderAction`, `UpdateUserProfile`).
    - Use single-purpose methods (`execute(...)` or `__invoke(...)`).
    - Handle database transactions (`DB::transaction(...)`), event dispatching, and external service calls here.
  - **Data Transfer Objects (DTOs - `app/DTOs`)**:
    - Use strongly-typed DTOs to transfer structured data between Form Requests and Actions. Avoid passing raw arrays or uncontrolled `$request->all()`.
  - **Persistence Layer (Eloquent Models & Scopes)**:
    - Keep models focused on relationships (`HasMany`, `BelongsTo`), attribute casts (`casts()`), and local query scopes.
    - Explicitly type-hint all relationship methods.
    - Strictly follow the [Query Optimization Guide](.agents/rules/query-optimization.md): prevent N+1 queries (`with`, `withCount`), avoid `SELECT *` on large tables, use `chunkById`/`cursorPaginate` for large datasets, and ensure SARGable indexed queries.
- **Clean Code Standards**:
  - Add `declare(strict_types=1);` to all new PHP files.
  - Explicitly type-hint all method parameters, property types, and return types. Avoid `mixed` where concrete types can be specified.
  - Follow PSR-12 / Laravel standards formatted via Laravel Pint (`./vendor/bin/pint`).
  - Favor early returns over nested `if/else` conditionals.
  - **Avoid Over-Engineering & Premature Abstraction**:
    - Write pragmatic, straightforward, and readable code (YAGNI - "You Aren't Gonna Need It").
    - NEVER create premature interfaces, abstract classes, generic factories, or repository layers when a single concrete Action or direct Eloquent model query is sufficient.
    - Do not introduce speculative design patterns or multi-level indirection for simple CRUD or operations with only one implementation.
  - **Strict Modularity & Single Responsibility (SRP)**:
    - Never dump everything into one file. Exactly one class per file.
    - Every class (Action, DTO, Model, Form Request, Policy) must have a single, well-defined responsibility with one reason to change.
    - Avoid kitchen-sink classes or god-objects; break complex domain processes into discrete, single-action classes.
- **Testing**:
  - Write granular feature and unit tests with **Pest PHP** (`./vendor/bin/pest`).
  - Test domain actions in isolation, and test HTTP endpoints for status, authorization, validation errors, and Inertia props assertions.

---

## 3. Clean Frontend Architecture (Vue 3, Inertia, TypeScript)

- **Component Hierarchy & Responsibilities**:
  - **Pages (`resources/js/pages/`)**: Route-level entry points rendered by Inertia. Coordinate layout, page titles/breadcrumbs, and pass typed props down to components. Avoid placing low-level DOM or complex state machines directly in page components.
  - **UI / Base Components (`resources/js/components/`)**: Reusable, modular, presentational components with well-defined props and emits. Keep components pure and decoupled from router/page specifics.
  - **Composables (`resources/js/composables/`)**: Extract reusable reactive logic, side-effects, timers, and browser APIs into custom composables (`useFeature()`).
- **TypeScript Conventions**:
  - Always use Vue 3 SFCs with `<script setup lang="ts">`.
  - Strictly define props with `defineProps<{ ... }>()` and emits with `defineEmits<{ ... }>()`.
  - Maintain centralized TypeScript interfaces in `resources/js/types/` corresponding directly to backend models and Inertia page payloads. Disallow untyped `any`.
- **UI & Styling**:
  - Build on Tailwind CSS utility classes and `radix-vue` primitives.
  - Follow the [Anti-Slop UI Skill](.agents/skills/anti-slop-ui-design/SKILL.md): avoid gratuitous purple gradients, card-in-card nesting, and generic AI aesthetic. Enforce high information density, crisp typography, tactile surfaces, and accessible empty/loading states.
  - Use `cn()` helper (`clsx` + `tailwind-merge`) for clean conditional styling.
  - Use `lucide-vue-next` for all UI icons.
- **Formatting & Linting**:
  - Run `npm run format` (`prettier --write resources/`) and `npm run lint` (`eslint . --fix`).
  - Type-check with `npx vue-tsc --noEmit`.

---

## 4. Task Execution Lifecycle & Communication

- **Step 1: Intake & Intent Verification (Before Starting)**:
  - When given a task, do NOT start modifying code immediately.
  - First, understand the user's intent and state what you understand in a clean, concise, no-fluff format.
  - Summarize the objectives and planned steps straight to the point.
- **Step 2: Mandatory Testing & Self-Correction (Before Concluding)**:
  - After completing changes, always execute tests first (`php artisan test` / Pest).
  - If any test fails or runtime error occurs, immediately fix it. Never report completion with failing tests.
- **Step 3: Completion Delivery & Proof of Verification (Upon Completion)**:
  - After accomplishing a task, report:
    1. **What was built**: New features, classes, actions, or components.
    2. **What was changed**: Specific files modified and the rationale.
    3. **Proof of Passing Tests**: A clean, structured summary (e.g., test suites count, passed tests, assertions, duration). **Do NOT dump raw terminal output**.

---

## 5. Verification Gate

Before marking any task complete, verify:
1. Backend tests pass: `php artisan test` (Pest PHP suite passes with 0 failures).
2. PHP code styling: `./vendor/bin/pint --test`.
3. Frontend lint & formatting: `npm run lint` and `npm run format:check`.
4. TypeScript validation: `npx vue-tsc --noEmit`.
5. Ensure no unhandled deprecation warnings or runtime exceptions occur.
