<script setup lang="ts">
import AssetMetricsHeader from '@/components/assets/AssetMetricsHeader.vue';
import AssetQrModal from '@/components/assets/AssetQrModal.vue';
import AssetStatusBadge from '@/components/assets/AssetStatusBadge.vue';
import AssignAssetModal from '@/components/assets/AssignAssetModal.vue';
import BatchQrPrintModal from '@/components/assets/BatchQrPrintModal.vue';
import CheckInAssetModal from '@/components/assets/CheckInAssetModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Asset, AssetMetrics, StatusOption, TypeOption, UserSummary } from '@/types/asset';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Download, HardDrive, LogIn, LogOut, Plus, Printer, QrCode, Search, X } from 'lucide-vue-next';
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
    assets: Paginated<Asset>;
    metrics: AssetMetrics;
    users: UserSummary[];
    filters: {
        search?: string | null;
        status?: string | null;
        type?: string | null;
    };
    statuses: StatusOption[];
    types: TypeOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: '/assets',
    },
];

const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const selectedType = ref(props.filters.type ?? '');

// Modals state
const assignModalOpen = ref(false);
const checkInModalOpen = ref(false);
const qrModalOpen = ref(false);
const batchPrintModalOpen = ref(false);
const activeAsset = ref<Asset | null>(null);
const qrAsset = ref<Asset | null>(null);

function openAssignModal(asset: Asset) {
    activeAsset.value = asset;
    assignModalOpen.value = true;
}

function openCheckInModal(asset: Asset) {
    activeAsset.value = asset;
    checkInModalOpen.value = true;
}

function openQrModal(asset: Asset) {
    qrAsset.value = asset;
    qrModalOpen.value = true;
}

function exportCsv() {
    const params = new URLSearchParams();
    if (search.value) params.set('q', search.value);
    if (selectedStatus.value) params.set('status', selectedStatus.value);
    if (selectedType.value) params.set('type', selectedType.value);

    const queryString = params.toString();
    window.location.href = route('assets.export') + (queryString ? `?${queryString}` : '');
}

function applyFilters() {
    router.get(
        route('assets.index'),
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            type: selectedType.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function setStatusFilter(statusVal: string) {
    selectedStatus.value = selectedStatus.value === statusVal ? '' : statusVal;
    applyFilters();
}

function clearFilters() {
    search.value = '';
    selectedStatus.value = '';
    selectedType.value = '';
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
</script>

<template>
    <Head title="IT Asset Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">IT Asset Inventory</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Track hardware lifecycle, active employee assignments, and custody history.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="batchPrintModalOpen = true"
                        :disabled="assets.data.length === 0"
                        class="gap-1.5 text-xs"
                    >
                        <Printer class="h-3.5 w-3.5" />
                        Print Labels
                    </Button>

                    <Button variant="outline" size="sm" @click="exportCsv" :disabled="assets.total === 0" class="gap-1.5 text-xs">
                        <Download class="h-3.5 w-3.5" />
                        Export CSV
                    </Button>

                    <Button as-child size="sm" class="text-xs">
                        <Link :href="route('assets.create')">
                            <Plus class="mr-1.5 h-3.5 w-3.5" />
                            Register Asset
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- KPI Metrics -->
            <AssetMetricsHeader :metrics="metrics" />

            <!-- Filters & Search Toolbar -->
            <div class="flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                        <Input
                            v-model="search"
                            type="search"
                            placeholder="Search by tag (AST-...), name, serial number..."
                            class="pl-9"
                            @keydown.enter="applyFilters"
                        />
                    </div>

                    <!-- Type Filter -->
                    <div class="flex items-center gap-2">
                        <select
                            v-model="selectedType"
                            @change="applyFilters"
                            class="h-9 rounded-md border border-neutral-200 bg-transparent px-3 py-1 text-xs shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                        >
                            <option value="">All Categories</option>
                            <option v-for="t in types" :key="t.value" :value="t.value">
                                {{ t.label }}
                            </option>
                        </select>

                        <Button
                            v-if="search || selectedStatus || selectedType"
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

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 border-t border-neutral-100 pt-2 text-xs dark:border-neutral-800">
                    <span class="mr-1 text-[11px] font-medium text-neutral-400">Status:</span>
                    <button
                        type="button"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            !selectedStatus
                                ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                                : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700',
                        ]"
                        @click="setStatusFilter('')"
                    >
                        All ({{ metrics.total }})
                    </button>
                    <button
                        v-for="s in statuses"
                        :key="s.value"
                        type="button"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            selectedStatus === s.value
                                ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                                : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700',
                        ]"
                        @click="setStatusFilter(s.value)"
                    >
                        {{ s.label }}
                    </button>
                </div>
            </div>

            <!-- Asset Inventory Table -->
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset Tag</th>
                                <th class="px-4 py-3">Hardware & Model</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Current Assignee</th>
                                <th class="px-4 py-3">Serial No.</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <!-- Empty State -->
                            <tr v-if="assets.data.length === 0">
                                <td colspan="7" class="py-12 text-center">
                                    <HardDrive class="mx-auto h-8 w-8 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No assets found</p>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        {{
                                            search || selectedStatus || selectedType
                                                ? 'Try adjusting your search filters.'
                                                : 'Register your first hardware asset to get started.'
                                        }}
                                    </p>
                                    <Button
                                        v-if="search || selectedStatus || selectedType"
                                        variant="outline"
                                        size="sm"
                                        class="mt-3 text-xs"
                                        @click="clearFilters"
                                    >
                                        Clear Filters
                                    </Button>
                                    <Button v-else as-child size="sm" class="mt-3 text-xs">
                                        <Link :href="route('assets.create')">Register Asset</Link>
                                    </Button>
                                </td>
                            </tr>

                            <!-- Asset Rows -->
                            <tr
                                v-for="asset in assets.data"
                                :key="asset.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <!-- Tag -->
                                <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold text-neutral-900 dark:text-neutral-100">
                                    <Link :href="route('assets.show', asset.id)" class="hover:underline">
                                        {{ asset.asset_tag }}
                                    </Link>
                                </td>

                                <!-- Name & Model -->
                                <td class="px-4 py-3">
                                    <Link
                                        :href="route('assets.show', asset.id)"
                                        class="font-medium text-neutral-900 hover:underline dark:text-neutral-100"
                                    >
                                        {{ asset.name }}
                                    </Link>
                                    <div v-if="asset.model_number" class="text-[11px] text-neutral-400">Model: {{ asset.model_number }}</div>
                                </td>

                                <!-- Type -->
                                <td class="whitespace-nowrap px-4 py-3 capitalize text-neutral-600 dark:text-neutral-400">
                                    {{ asset.type }}
                                </td>

                                <!-- Status Badge -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <AssetStatusBadge :status="asset.status" />
                                </td>

                                <!-- Assignee -->
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div v-if="asset.current_assignment?.user" class="flex items-center gap-2">
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-neutral-100 text-[10px] font-bold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                        >
                                            {{ asset.current_assignment.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ asset.current_assignment.user.name }}
                                            </div>
                                            <div class="text-[10px] text-neutral-400">
                                                Since {{ formatDate(asset.current_assignment.assigned_at) }}
                                            </div>
                                        </div>
                                    </div>
                                    <span v-else class="italic text-neutral-400"> Unassigned </span>
                                </td>

                                <!-- Serial Number -->
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ asset.serial_number ?? '—' }}
                                </td>

                                <!-- Quick Actions -->
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Check Out Button -->
                                        <Button
                                            v-if="asset.status === 'available'"
                                            size="sm"
                                            variant="outline"
                                            class="h-7 border-emerald-300 text-xs text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                                            @click="openAssignModal(asset)"
                                        >
                                            <LogOut class="mr-1 h-3 w-3" />
                                            Check Out
                                        </Button>

                                        <!-- Check In Button -->
                                        <Button
                                            v-else-if="asset.status === 'assigned'"
                                            size="sm"
                                            variant="outline"
                                            class="h-7 border-blue-300 text-xs text-blue-700 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-950/40"
                                            @click="openCheckInModal(asset)"
                                        >
                                            <LogIn class="mr-1 h-3 w-3" />
                                            Check In
                                        </Button>

                                        <!-- QR Code Button -->
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="h-7 w-7 p-0 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                                            title="View / Print QR Code"
                                            @click="openQrModal(asset)"
                                        >
                                            <QrCode class="h-3.5 w-3.5" />
                                        </Button>

                                        <!-- View Link -->
                                        <Button as-child size="sm" variant="ghost" class="h-7 text-xs">
                                            <Link :href="route('assets.show', asset.id)"> Details </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                <div
                    v-if="assets.last_page > 1"
                    class="flex items-center justify-between border-t border-neutral-200 px-4 py-3 dark:border-neutral-800"
                >
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        Showing page <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ assets.current_page }}</span> of
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ assets.last_page }}</span>
                        ({{ assets.total }} items)
                    </div>

                    <div class="flex items-center gap-1">
                        <Button as-child variant="outline" size="sm" :disabled="!assets.prev_page_url" class="h-8 w-8 p-0">
                            <Link v-if="assets.prev_page_url" :href="assets.prev_page_url">
                                <ChevronLeft class="h-4 w-4" />
                            </Link>
                            <span v-else class="text-neutral-300 dark:text-neutral-600">
                                <ChevronLeft class="h-4 w-4" />
                            </span>
                        </Button>

                        <Button as-child variant="outline" size="sm" :disabled="!assets.next_page_url" class="h-8 w-8 p-0">
                            <Link v-if="assets.next_page_url" :href="assets.next_page_url">
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

        <!-- Modals -->
        <AssignAssetModal v-model:open="assignModalOpen" :asset="activeAsset" :users="users" />

        <CheckInAssetModal v-model:open="checkInModalOpen" :asset="activeAsset" />

        <AssetQrModal v-model:open="qrModalOpen" :asset="qrAsset" />

        <BatchQrPrintModal v-model:open="batchPrintModalOpen" :assets="assets.data" />
    </AppLayout>
</template>
