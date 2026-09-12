# Clean Architecture & Clean Code Standards

This document establishes the architecture and design patterns for the project.

---

## 1. Clean Backend Architecture (Laravel 12)

### The Layered Flow
```
HTTP Request 
   └──> Route 
         └──> Form Request (Authorize & Validate)
               └──> Controller (Extract DTO, Thin Orchestrator)
                     └──> Action / Service (Business Logic, Transactions)
                           └──> Eloquent Model / Database
```

### Layer Responsibilities

#### 1. Form Requests (`app/Http/Requests`)
- Responsible for **input validation** and **authorization policies**.
- Provide a `toDto(): DataTransferObject` method or pass validated data to DTOs.
- Never let raw, unvalidated `$request->input()` reach domain actions.

#### 2. Controllers (`app/Http/Controllers`)
- **Strictly Thin**: 3 to 10 lines per method.
- Never write `DB::transaction()`, business calculations, or multi-step mutations directly in controllers.
- Delegate immediately to an Action and return an `Inertia::render(...)` or `RedirectResponse`.

```php
// Good Controller Example
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $order = $action->execute($request->toDto(), $request->user());

        return redirect()->route('orders.show', $order);
    }
}
```

#### 3. Actions (`app/Actions`)
- Follow the **Single Responsibility Principle (SRP)**.
- One class per discrete business operation (`CreateUserAction`, `UpdateOrderAction`, `CancelSubscriptionAction`).
- Standard method signature: `public function execute(...)`.
- Wrap complex or multi-table updates in `DB::transaction(...)`.
- Dispatch domain events when state changes occur (`Event::dispatch(...)`).

#### 4. Data Transfer Objects (DTOs - `app/DTOs`)
- Immutable typed data containers using PHP readonly properties.
- Guarantee strict type safety between HTTP and Domain layers.

```php
namespace App\DTOs;

readonly class OrderData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?string $notes = null,
    ) {}
}
```

#### 5. Eloquent Models (`app/Models`)
- Keep models focused on persistence, relationship definitions, and attribute casting.
- Extract complex queries into **Local Scopes** (e.g. `scopeActive()`) or dedicated Query Builder classes.
- Always declare return types on relationship methods (`BelongsTo`, `HasMany`).

---

## 2. Clean Frontend Architecture (Vue 3 + Inertia)

### Separation of Concerns
1. **Pages (`resources/js/pages/`)**:
   - Act as route orchestrators.
   - Receive props from Inertia, assemble high-level layouts, and pass props to child components.
   - Avoid deep business logic or low-level DOM manipulations directly in page files.
2. **Components (`resources/js/components/`)**:
   - Small, reusable, and single-purpose.
   - Communicate strictly via typed props and emits.
3. **Composables (`resources/js/composables/`)**:
   - Reusable reactive functions (`useSomething()`).
   - Extract stateful logic, debounce timers, local storage handling, or API interactions.

### Clean Code Practices (Vue & TypeScript)
- **Zero `any`**: Explicitly type all props, emits, ref variables, and API responses.
- **Early Return Pattern**: Guard clauses over deeply nested template conditionals.
- **Descriptive Naming**: Use intention-revealing names for functions, props, and components.
- **Consistent Icons**: Always import icons from `lucide-vue-next`.

---

## 3. Pragmatic Architecture over Over-Engineering (YAGNI)

- **Avoid Premature Abstraction**:
  - Do NOT create interfaces or abstract base classes when only one concrete implementation exists.
  - Do NOT create repository patterns or service providers for simple Eloquent queries; Laravel's Eloquent Active Record pattern already serves as an expressive abstraction.
  - Do NOT build custom generic factory pipelines or complex design patterns (Strategy, Decorator, Visitor) for simple CRUD operations.
- **Solve Today's Problem Cleanly**:
  - Focus on code readability, directness, and immediate maintainability over hypothetical future extensibility.
  - Refactor towards abstractions only when code duplication occurs at least 3 times or when business requirements explicitly diverge into multiple drivers/strategies.
