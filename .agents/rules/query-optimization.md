# Database Query Optimization Guide

This guide defines performance rules and best practices for writing optimized database queries with Eloquent and the Laravel Query Builder.

---

## 1. Eliminate N+1 Query Problems

### Eager Loading
Always eager load relationships when accessing child relations across collections.
```php
// ❌ Bad: Causes 1 query for users + N queries for each user's profile
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->bio;
}

// ✅ Good: Executes exactly 2 optimized queries
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->bio;
}
```

### Relationship Aggregates (`withCount`, `withExists`)
Never load full relationship collections solely to count records or check existence.
```php
// ❌ Bad: Loads entire collection of comments into memory
$posts = Post::with('comments')->get();
$count = $posts->first()->comments->count();

// ✅ Good: Subquery runs in a single SQL query with minimal memory footprint
$posts = Post::withCount('comments')
    ->withExists('likes')
    ->get();

$count = $posts->first()->comments_count;
$hasLiked = $posts->first()->likes_exists;
```

### Lazy Loading Prevention in Development
Enforce strict lazy loading protection in `AppServiceProvider::boot()`:
```php
Model::preventLazyLoading(! app()->isProduction());
```

---

## 2. Column Selection & Memory Management

### Select Only Required Columns
Avoid `SELECT *`, especially on wide tables containing large text, JSON, or audit fields.
```php
// ❌ Bad: Pulls all columns for every row
$users = User::all();

// ✅ Good: Fetches only necessary attributes
$users = User::query()
    ->select(['id', 'name', 'email'])
    ->get();
```

> [!IMPORTANT]
> When selecting specific columns with eager loading, **always include the foreign and primary keys** required to link the relationship:
> ```php
> // Both 'id' (parent PK) and 'user_id' (child FK) must be present
> $users = User::query()
>     ->select(['id', 'name'])
>     ->with(['posts:id,user_id,title'])
>     ->get();
> ```

---

## 3. High-Performance Pagination & Chunking

### Choose the Right Pagination Strategy
1. **`paginate($perPage)`**: Runs a `COUNT(*)` query + a limit/offset query. Best for standard admin tables where total page count is required.
2. **`simplePaginate($perPage)`**: Omits the expensive `COUNT(*)` query. Executes a single `LIMIT perPage + 1` query. Ideal for mobile feeds or high-traffic listings.
3. **`cursorPaginate($perPage)`**: The fastest pagination method for large datasets. Uses indexed pointer seeks (`WHERE id > ? LIMIT ?`) instead of high SQL `OFFSET` values.

```php
// ✅ Best for high-volume infinite scroll or large data tables
$orders = Order::query()
    ->where('user_id', $user->id)
    ->orderByDesc('id')
    ->cursorPaginate(20);
```

### Processing Large Datasets (Chunking)
Never use `Model::all()` or high-memory `get()` in console commands, jobs, or exports.
```php
// ❌ Bad: Memory scales linearly with row count, causing out-of-memory crashes
foreach (User::all() as $user) { ... }

// ❌ Bad: Standard chunk() can skip or re-process rows if rows are modified in the loop
User::chunk(500, function ($users) { ... });

// ✅ Good: chunkById uses indexed primary key comparisons to prevent offset drift
User::where('is_active', false)->chunkById(500, function ($users) {
    foreach ($users as $user) {
        $user->update(['status' => 'archived']);
    }
});

// ✅ Best for memory-efficient lazy iteration
foreach (User::query()->lazyById(500) as $user) {
    // Process single user at a time
}
```

---

## 4. Index Utilization & Search Optimization

### SARGable Queries (Search Argument Able)
Do not wrap indexed columns in SQL functions inside `WHERE` clauses. Doing so prevents the database from using B-Tree indexes.
```php
// ❌ Bad: Applying DATE() disables the index on created_at (Full Table Scan)
$orders = Order::whereRaw('DATE(created_at) = ?', ['2026-09-12'])->get();

// ✅ Good: Index seek using range comparison
$orders = Order::whereBetween('created_at', [
    '2026-09-12 00:00:00',
    '2026-09-12 23:59:59',
])->get();
```

### Index Guidelines for Migrations
- Add indexes on foreign keys: `$table->foreignId('user_id')->constrained()->index()`.
- Add composite indexes for queries filtering or sorting across multiple columns:
  ```php
  // Matches: WHERE status = ? AND created_at >= ?
  $table->index(['status', 'created_at']);
  ```
- Put high-cardinality or equality filter columns first in composite indexes.

---

## 5. Aggregations & Existence Checks

Always perform calculations inside the database rather than fetching records into PHP memory.
```php
// ❌ Bad: Fetches rows into memory to count
$hasOrders = $user->orders()->get()->isNotEmpty();
$total = $user->orders()->get()->sum('amount');

// ✅ Good: Executes lightweight SQL statements
$hasOrders = $user->orders()->exists();
$total = (float) $user->orders()->sum('amount');
```

---

## 6. Bulk Mutations & Upserts

Avoid running single insert or update queries inside loops.
```php
// ❌ Bad: Generates N insert queries
foreach ($items as $item) {
    OrderItem::create($item);
}

// ✅ Good: Single bulk insert
OrderItem::insert($items);

// ✅ Good: Efficient upsert (Insert or Update on duplicate key)
Product::upsert(
    $productsArray,
    uniqueBy: ['sku'],
    update: ['price', 'stock', 'updated_at']
);
```

---

## 7. Query Inspection & Execution Plans

Always inspect queries before completing complex database features:
```php
// 1. Output raw SQL and bindings
$query->dumpRawSql();

// 2. Output EXPLAIN execution plan to verify index hits
$plan = $query->explain();
dd($plan);
```
