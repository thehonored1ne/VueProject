# Code Templates for Inertia Features

## 1. Controller Template
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Items/Index', [
            'items' => Item::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $request->user()->items()->create($request->validated());

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }
}
```

## 2. Vue 3 SFC Template (`resources/js/pages/Items/Index.vue`)
```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

interface Item {
    id: number;
    title: string;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Items',
        href: '/items',
    },
];

defineProps<{
    items: {
        data: Item[];
    };
}>();
</script>

<template>
    <Head title="Items" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-foreground">Items</h1>
                <Link
                    :href="route('items.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="size-4" />
                    New Item
                </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <div v-if="items.data.length === 0" class="py-8 text-center text-muted-foreground">
                    No items found.
                </div>
                <ul v-else class="divide-y divide-border">
                    <li v-for="item in items.data" :key="item.id" class="py-3">
                        <span class="font-medium text-foreground">{{ item.title }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
```

## 3. Pest Feature Test Template (`tests/Feature/ItemTest.php`)
```php
<?php

use App\Models\Item;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected when attempting to view items', function () {
    $this->get(route('items.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view their items', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Items/Index')
            ->has('items.data')
        );
});
```
