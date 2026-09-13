<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Employee, EmployeeMetrics } from '@/types/employee';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    ChevronRight as ChevronRightIcon,
    Edit,
    Key,
    Laptop,
    Search,
    ShieldCheck,
    Trash2,
    UserPlus,
    Users,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    employees: Paginated<Employee>;
    metrics: EmployeeMetrics;
    filters: {
        search?: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
];

const search = ref(props.filters.search ?? '');

function applyFilters() {
    router.get(
        route('employees.index'),
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    search.value = '';
    applyFilters();
}

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function deleteEmployee(emp: Employee) {
    if (confirm(`Are you sure you want to delete ${emp.name}? This action cannot be undone.`)) {
        router.delete(route('employees.destroy', emp.id));
    }
}
</script>

<template>
    <Head title="Employee Directory & Custody" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">Employee Directory & Custody</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Monitor team members, assigned hardware, software license seat footprint, and custody status.
                    </p>
                </div>

                <Button as-child size="sm" class="h-9 gap-1.5 text-xs">
                    <Link :href="route('employees.create')">
                        <UserPlus class="h-3.5 w-3.5" />
                        Add Team Member
                    </Link>
                </Button>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <!-- Total Employees -->
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Team Members</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ metrics.total_employees }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Active personnel registered</p>
                </div>

                <!-- In Custody -->
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Holding Assets / Seats</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                        >
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ metrics.active_custody_employees }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Employees with active assignments</p>
                </div>

                <!-- Hardware Devices -->
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Assigned Hardware</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                        >
                            <Laptop class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ metrics.total_assigned_assets }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Physical devices in employee custody</p>
                </div>

                <!-- Software Seats -->
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Allocated Software Seats</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400"
                        >
                            <Key class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ metrics.total_assigned_licenses }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Active seat assignments</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-neutral-400" />
                    <Input
                        v-model="search"
                        placeholder="Search employee by name or email..."
                        class="h-9 pl-9 text-xs"
                        @keydown.enter="applyFilters"
                    />
                </div>

                <div v-if="search" class="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="h-9 text-xs text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100"
                        @click="clearFilters"
                    >
                        <X class="mr-1 h-3.5 w-3.5" />
                        Reset Search
                    </Button>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Team Member</th>
                                <th class="px-4 py-3">Hardware in Custody</th>
                                <th class="px-4 py-3">Software Licenses</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <!-- Empty State -->
                            <tr v-if="employees.data.length === 0">
                                <td colspan="5" class="py-12 text-center">
                                    <Users class="mx-auto h-8 w-8 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No team members found</p>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ search ? 'No employees match your search query.' : 'No employee records are present in the system.' }}
                                    </p>
                                    <Button v-if="search" variant="outline" size="sm" class="mt-3 text-xs" @click="clearFilters">
                                        Clear Search
                                    </Button>
                                </td>
                            </tr>

                            <!-- Data Rows -->
                            <tr
                                v-for="employee in employees.data"
                                :key="employee.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <!-- Employee Info -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-neutral-200 bg-neutral-100 text-xs font-semibold text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                                        >
                                            {{ getInitials(employee.name) }}
                                        </div>
                                        <div>
                                            <Link
                                                :href="route('employees.show', employee.id)"
                                                class="font-semibold text-neutral-900 hover:underline dark:text-neutral-100"
                                            >
                                                {{ employee.name }}
                                            </Link>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ employee.email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Hardware in Custody -->
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-[11px] font-medium',
                                            (employee.active_assets_count ?? 0) > 0
                                                ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300'
                                                : 'border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-400',
                                        ]"
                                    >
                                        <Laptop class="h-3 w-3" />
                                        {{ employee.active_assets_count ?? 0 }} {{ (employee.active_assets_count ?? 0) === 1 ? 'device' : 'devices' }}
                                    </span>
                                </td>

                                <!-- Software Licenses -->
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-[11px] font-medium',
                                            (employee.active_licenses_count ?? 0) > 0
                                                ? 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300'
                                                : 'border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-400',
                                        ]"
                                    >
                                        <Key class="h-3 w-3" />
                                        {{ employee.active_licenses_count ?? 0 }} {{ (employee.active_licenses_count ?? 0) === 1 ? 'seat' : 'seats' }}
                                    </span>
                                </td>

                                <!-- Joined Date -->
                                <td class="px-4 py-3 tabular-nums text-neutral-600 dark:text-neutral-400">
                                    {{ formatDate(employee.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button as-child variant="outline" size="sm" class="h-7 gap-1 text-xs">
                                            <Link :href="route('employees.show', employee.id)">
                                                <span>Custody 360°</span>
                                                <ChevronRightIcon class="h-3 w-3" />
                                            </Link>
                                        </Button>

                                        <Button
                                            as-child
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0 text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100"
                                        >
                                            <Link :href="route('employees.edit', employee.id)" title="Edit Employee">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Link>
                                        </Button>

                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0 text-neutral-500 hover:text-rose-600 dark:hover:text-rose-400"
                                            title="Delete Employee"
                                            @click="deleteEmployee(employee)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="employees.total > employees.per_page"
                    class="flex items-center justify-between border-t border-neutral-200 px-4 py-3 dark:border-neutral-800"
                >
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Showing
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                            {{ (employees.current_page - 1) * employees.per_page + 1 }}
                        </span>
                        to
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                            {{ Math.min(employees.current_page * employees.per_page, employees.total) }}
                        </span>
                        of
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ employees.total }}</span>
                        employees
                    </p>

                    <div class="flex items-center gap-1">
                        <Button variant="outline" size="sm" class="h-8 w-8 p-0" :disabled="!employees.prev_page_url" as-child>
                            <Link v-if="employees.prev_page_url" :href="employees.prev_page_url">
                                <ChevronLeft class="h-4 w-4" />
                            </Link>
                            <span v-else><ChevronLeft class="h-4 w-4 opacity-50" /></span>
                        </Button>

                        <Button variant="outline" size="sm" class="h-8 w-8 p-0" :disabled="!employees.next_page_url" as-child>
                            <Link v-if="employees.next_page_url" :href="employees.next_page_url">
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                            <span v-else><ChevronRight class="h-4 w-4 opacity-50" /></span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
