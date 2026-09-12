# Laravel Backend Rules

## PHP & Framework Standards
- **PHP Version Target**: PHP 8.5 on Laravel Herd, while maintaining backward compatibility with PHP 8.2+.
- **Strict Types**: Add `declare(strict_types=1);` to all PHP files.
- **Clean Architecture Layers**:
  - **Controllers**: Thin HTTP orchestrators only. No business logic or direct DB mutations.
  - **Actions (`app/Actions`)**: Single-responsibility domain actions with `execute(...)` methods.
  - **DTOs (`app/DTOs`)**: Readonly typed classes for passing validated data.
  - **Form Requests (`app/Http/Requests`)**: Dedicated validation and authorization.
- **Naming Conventions**:
  - Controllers: PascalCase ending in `Controller` (e.g., `OrderController`).
  - Actions: PascalCase verb-noun ending in `Action` (e.g., `CreateOrderAction`).
  - DTOs: PascalCase ending in `Data` or `DTO` (e.g., `OrderData`).
  - Models: Singular PascalCase (e.g., `Order`, `Product`).
  - Migrations: Descriptive snake_case matching Artisan conventions (`create_orders_table`).
  - Form Requests: PascalCase ending in `Request` (e.g., `StoreOrderRequest`).

## Eloquent & Database
- Use `$casts` array or `casts()` method with built-in cast types: `hashed`, `datetime`, `boolean`, `array`.
- Always specify foreign key constraints with cascade behaviors where appropriate (`cascadeOnDelete()`, `nullOnDelete()`).
- Avoid raw SQL queries when Eloquent or query builder can accomplish the task.
- **Query Performance Rules**:
  - Eliminate N+1 queries: use `with()` or `load()` for relations; use `withCount()` or `withExists()` for aggregates.
  - Avoid `SELECT *`: select only required columns (always retaining foreign and primary keys).
  - Use `chunkById()` or `lazyById()` for batch operations; use `cursorPaginate()` for large datasets.
  - SARGable queries: never wrap indexed columns in SQL functions (e.g. `whereDate()`); use `whereBetween()`.
  - See [query-optimization.md](query-optimization.md) for detailed patterns.

## Testing & Quality
- Write tests using Pest (`it('...', function () { ... })` syntax).
- Maintain test coverage for all HTTP endpoints, ensuring both authorization and validation error scenarios are covered.
- Run Laravel Pint (`./vendor/bin/pint`) before finishing any PHP edits.
