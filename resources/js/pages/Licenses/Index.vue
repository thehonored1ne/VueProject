<script setup lang="ts">
import AssignSeatModal from '@/components/licenses/AssignSeatModal.vue';
import LicenseMetricsHeader from '@/components/licenses/LicenseMetricsHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { UserSummary } from '@/types/asset';
import type { BillingCycleOption, LicenseMetrics, SoftwareLicense } from '@/types/license';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Key, Plus, Search, UserPlus, X } from 'lucide-vue-next';
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
    licenses: Paginated<SoftwareLicense>;
    metrics: LicenseMetrics;
    vendors: string[];
    users: UserSummary[];
    filters: {
        search?: string | null;
        vendor?: string | null;
    };
    billingCycles: BillingCycleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Licenses',
        href: '/licenses',
    },
];

const search = ref(props.filters.search ?? '');
const selectedVendor = ref(props.filters.vendor ?? '');

const assignModalOpen = ref(false);
const activeLicense = ref<SoftwareLicense | null>(null);

function openAssignModal(license: SoftwareLicense) {
    activeLicense.value = license;
    assignModalOpen.value = true;
}

function applyFilters() {
    router.get(
        route('licenses.index'),
        {
            search: search.value || undefined,
            vendor: selectedVendor.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    search.value = '';
    selectedVendor.value = '';
    applyFilters();
}

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return 'Perpetual';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatCurrency(val?: string | number | null): string {
    if (val === null || val === undefined || val === '') return '—';
    const num = Number(val);
    return isNaN(num) ? '—' : `$${num.toFixed(2)}`;
}

function getExpirationStatus(dateStr?: string | null) {
    if (!dateStr) return null;
    const expires = new Date(dateStr);
    const now = new Date();
    const diffDays = Math.ceil((expires.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return { label: 'Expired', class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60' };
    } else if (diffDays <= 30) {
        return {
            label: `${diffDays}d left`,
            class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
        };
    }
    return null;
}
</script>

<template>
    <Head title="Software Licenses & Subscriptions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">
                        Software & Subscription Licenses
                    </h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Track seat allocations, subscription limits, and renewals across teams.
                    </p>
                </div>

                <Button as-child>
                    <Link :href="route('licenses.create')">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Register License
                    </Link>
                </Button>
            </div>

            <!-- KPI Metrics -->
            <LicenseMetricsHeader :metrics="metrics" />

            <!-- Filters Toolbar -->
            <div
                class="flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <Input v-model="search" type="search" placeholder="Search software or vendor..." class="pl-9" @keydown.enter="applyFilters" />
                </div>

                <div class="flex items-center gap-2">
                    <select
                        v-model="selectedVendor"
                        @change="applyFilters"
                        class="h-9 rounded-md border border-neutral-200 bg-white px-3 py-1 text-xs text-neutral-900 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus-visible:ring-neutral-300"
                    >
                        <option value="">All Vendors</option>
                        <option v-for="v in vendors" :key="v" :value="v">
                            {{ v }}
                        </option>
                    </select>

                    <Button
                        v-if="search || selectedVendor"
                        variant="ghost"
                        size="sm"
                        class="h-9 text-xs text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100"
                        @click="clearFilters"
                    >
                        <X class="mr-1 h-3.5 w-3.5" />
                        Reset
                    </Button>
                </div>
            </div>

            <!-- License Table -->
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Software & Publisher</th>
                                <th class="px-4 py-3">Seat Utilization</th>
                                <th class="px-4 py-3">Billing Cycle</th>
                                <th class="px-4 py-3">Cost / Seat</th>
                                <th class="px-4 py-3">Renewal Date</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <!-- Empty State -->
                            <tr v-if="licenses.data.length === 0">
                                <td colspan="6" class="py-12 text-center">
                                    <Key class="mx-auto h-8 w-8 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No software licenses found</p>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        {{
                                            search || selectedVendor
                                                ? 'Try adjusting your search criteria.'
                                                : 'Register a software license or subscription to start tracking seats.'
                                        }}
                                    </p>
                                    <Button v-if="search || selectedVendor" variant="outline" size="sm" class="mt-3 text-xs" @click="clearFilters">
                                        Clear Filters
                                    </Button>
                                    <Button v-else as-child size="sm" class="mt-3 text-xs">
                                        <Link :href="route('licenses.create')">Register License</Link>
                                    </Button>
                                </td>
                            </tr>

                            <!-- Data Rows -->
                            <tr
                                v-for="lic in licenses.data"
                                :key="lic.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <!-- Name & Vendor -->
                                <td class="px-4 py-3">
                                    <Link
                                        :href="route('licenses.show', lic.id)"
                                        class="font-semibold text-neutral-900 hover:underline dark:text-neutral-100"
                                    >
                                        {{ lic.name }}
                                    </Link>
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                        {{ lic.vendor }}
                                    </div>
                                </td>

                                <!-- Seat Utilization -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ lic.assignments_count ?? 0 }} / {{ lic.seats_total }}
                                        </span>
                                        <span
                                            v-if="(lic.assignments_count ?? 0) >= lic.seats_total"
                                            class="rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"
                                        >
                                            Full
                                        </span>
                                    </div>
                                    <div class="mt-1.5 h-1.5 w-32 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                        <div
                                            :class="[
                                                'h-full transition-all',
                                                (lic.assignments_count ?? 0) >= lic.seats_total ? 'bg-rose-500' : 'bg-blue-600 dark:bg-blue-500',
                                            ]"
                                            :style="{
                                                width: `${Math.min(100, Math.round(((lic.assignments_count ?? 0) / lic.seats_total) * 100))}%`,
                                            }"
                                        />
                                    </div>
                                </td>

                                <!-- Billing Cycle -->
                                <td class="whitespace-nowrap px-4 py-3 capitalize text-neutral-600 dark:text-neutral-400">
                                    <span
                                        class="inline-flex items-center rounded-md border border-neutral-200 px-2 py-0.5 text-[11px] font-medium dark:border-neutral-800"
                                    >
                                        {{ lic.billing_cycle }}
                                    </span>
                                </td>

                                <!-- Cost -->
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-neutral-800 dark:text-neutral-200">
                                    {{ formatCurrency(lic.cost_per_seat) }}
                                </td>

                                <!-- Renewal Date -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-neutral-700 dark:text-neutral-300">
                                            {{ formatDate(lic.expires_at) }}
                                        </span>
                                        <span
                                            v-if="getExpirationStatus(lic.expires_at)"
                                            :class="[
                                                'rounded border px-1.5 py-0.5 text-[10px] font-medium',
                                                getExpirationStatus(lic.expires_at)?.class,
                                            ]"
                                        >
                                            {{ getExpirationStatus(lic.expires_at)?.label }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button
                                            v-if="(lic.assignments_count ?? 0) < lic.seats_total"
                                            size="sm"
                                            variant="outline"
                                            class="h-7 border-blue-200 text-xs text-blue-700 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-950/40"
                                            @click="openAssignModal(lic)"
                                        >
                                            <UserPlus class="mr-1 h-3 w-3" />
                                            Allocate
                                        </Button>

                                        <Button as-child size="sm" variant="ghost" class="h-7 text-xs">
                                            <Link :href="route('licenses.show', lic.id)"> Details </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="licenses.last_page > 1"
                    class="flex items-center justify-between border-t border-neutral-200 px-4 py-3 dark:border-neutral-800"
                >
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        Page <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ licenses.current_page }}</span> of
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ licenses.last_page }}</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <Button as-child variant="outline" size="sm" :disabled="!licenses.prev_page_url" class="h-8 w-8 p-0">
                            <Link v-if="licenses.prev_page_url" :href="licenses.prev_page_url">
                                <ChevronLeft class="h-4 w-4" />
                            </Link>
                            <span v-else class="text-neutral-300 dark:text-neutral-600">
                                <ChevronLeft class="h-4 w-4" />
                            </span>
                        </Button>

                        <Button as-child variant="outline" size="sm" :disabled="!licenses.next_page_url" class="h-8 w-8 p-0">
                            <Link v-if="licenses.next_page_url" :href="licenses.next_page_url">
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                            <span v-else class="text-neutral-300 dark:text-neutral-600">
                                <ChevronRight class="h-4 w-4" />
                            </span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <AssignSeatModal v-model:open="assignModalOpen" :license="activeLicense" :users="users" />
    </AppLayout>
</template>
