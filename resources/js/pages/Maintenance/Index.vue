<script setup lang="ts">
import CompleteMaintenanceModal from '@/components/maintenance/CompleteMaintenanceModal.vue';
import MaintenanceMetricsHeader from '@/components/maintenance/MaintenanceMetricsHeader.vue';
import MaintenanceTicketModal from '@/components/maintenance/MaintenanceTicketModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { AssetMaintenance, LicenseAlertItem, MaintenanceMetrics, WarrantyAlertItem } from '@/types/maintenance';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Clock, KeyRound, Plus, ShieldAlert, Wrench, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface SimpleAsset {
    id: number;
    asset_tag: string;
    name: string;
    status: string;
}

defineProps<{
    activeRepairs: AssetMaintenance[];
    recentCompleted: AssetMaintenance[];
    warrantyAlerts: WarrantyAlertItem[];
    licenseAlerts: LicenseAlertItem[];
    metrics: MaintenanceMetrics;
    availableAssets: SimpleAsset[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Maintenance & Alerts',
        href: '/maintenance',
    },
];

const activeTab = ref<'repairs' | 'warranties' | 'licenses' | 'history'>('repairs');

// Modals
const ticketModalOpen = ref(false);
const completeModalOpen = ref(false);
const activeRepairToComplete = ref<AssetMaintenance | null>(null);

function openCompleteModal(repair: AssetMaintenance) {
    activeRepairToComplete.value = repair;
    completeModalOpen.value = true;
}

function cancelRepair(repair: AssetMaintenance) {
    if (confirm(`Cancel maintenance ticket for ${repair.asset?.asset_tag}?`)) {
        router.post(route('maintenance.cancel', repair.id));
    }
}

function formatCurrency(val?: string | number | null): string {
    if (val === null || val === undefined || val === '') return '—';
    const num = Number(val);
    return isNaN(num) ? '—' : `$${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
    <Head title="Hardware Maintenance & Expiration Alerts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Maintenance & Alerts Center</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Track equipment repairs, hardware servicing expenses, and upcoming warranty or contract renewals.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button @click="ticketModalOpen = true" class="gap-1.5 text-xs">
                        <Plus class="h-3.5 w-3.5" />
                        Log Repair Job
                    </Button>
                </div>
            </div>

            <!-- Summary KPI Cards -->
            <MaintenanceMetricsHeader :metrics="metrics" />

            <!-- Tab Navigation Bar -->
            <div class="flex border-b border-neutral-200 text-xs dark:border-neutral-800">
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 border-b-2 px-4 py-2.5 font-medium transition-colors',
                        activeTab === 'repairs'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                    ]"
                    @click="activeTab = 'repairs'"
                >
                    <Wrench class="h-3.5 w-3.5" />
                    Active Repairs ({{ activeRepairs.length }})
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 border-b-2 px-4 py-2.5 font-medium transition-colors',
                        activeTab === 'warranties'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                    ]"
                    @click="activeTab = 'warranties'"
                >
                    <ShieldAlert class="h-3.5 w-3.5" />
                    Warranty Alerts ({{ warrantyAlerts.length }})
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 border-b-2 px-4 py-2.5 font-medium transition-colors',
                        activeTab === 'licenses'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                    ]"
                    @click="activeTab = 'licenses'"
                >
                    <KeyRound class="h-3.5 w-3.5" />
                    License Renewals ({{ licenseAlerts.length }})
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 border-b-2 px-4 py-2.5 font-medium transition-colors',
                        activeTab === 'history'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                    ]"
                    @click="activeTab = 'history'"
                >
                    <Clock class="h-3.5 w-3.5" />
                    Recent History ({{ recentCompleted.length }})
                </button>
            </div>

            <!-- Tab 1: Active Repairs Table -->
            <div
                v-if="activeTab === 'repairs'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset</th>
                                <th class="px-4 py-3">Repair Summary</th>
                                <th class="px-4 py-3">Service Provider</th>
                                <th class="px-4 py-3">Started</th>
                                <th class="px-4 py-3">Cost</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="activeRepairs.length === 0">
                                <td colspan="7" class="py-12 text-center text-neutral-500">
                                    <CheckCircle2 class="mx-auto h-8 w-8 text-emerald-500/70" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">All equipment operational</p>
                                    <p class="mt-1 text-xs text-neutral-400">No active maintenance or repair jobs currently in progress.</p>
                                </td>
                            </tr>

                            <tr
                                v-for="repair in activeRepairs"
                                :key="repair.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold">
                                    <Link
                                        v-if="repair.asset"
                                        :href="route('assets.show', repair.asset.id)"
                                        class="text-neutral-900 hover:underline dark:text-neutral-100"
                                    >
                                        {{ repair.asset.asset_tag }}
                                    </Link>
                                    <div v-if="repair.asset" class="font-sans text-[11px] font-normal text-neutral-500">{{ repair.asset.name }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ repair.title }}</div>
                                    <div v-if="repair.notes" class="mt-0.5 line-clamp-1 text-[11px] text-neutral-400">{{ repair.notes }}</div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-neutral-700 dark:text-neutral-300">
                                    {{ repair.provider }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-neutral-500">
                                    {{ formatDate(repair.started_at) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono text-neutral-800 dark:text-neutral-200">
                                    {{ formatCurrency(repair.cost) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium uppercase',
                                            repair.status === 'in_progress'
                                                ? 'border border-blue-200 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                                                : 'border border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                                        ]"
                                    >
                                        {{ repair.status.replace('_', ' ') }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="h-7 border-emerald-300 text-xs text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300"
                                            @click="openCompleteModal(repair)"
                                        >
                                            <CheckCircle2 class="mr-1 h-3 w-3" />
                                            Complete
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="h-7 text-xs text-neutral-400 hover:text-rose-600"
                                            @click="cancelRepair(repair)"
                                        >
                                            <XCircle class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Warranty Alerts Table -->
            <div
                v-else-if="activeTab === 'warranties'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset Tag</th>
                                <th class="px-4 py-3">Hardware & Model</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Warranty Expiration</th>
                                <th class="px-4 py-3">Alert Urgency</th>
                                <th class="px-4 py-3">Current Assignee</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="warrantyAlerts.length === 0">
                                <td colspan="7" class="py-12 text-center text-neutral-500">
                                    <CheckCircle2 class="mx-auto h-8 w-8 text-emerald-500/70" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No warranty expirations</p>
                                    <p class="mt-1 text-xs text-neutral-400">
                                        All registered hardware warranties are healthy and valid beyond 60 days.
                                    </p>
                                </td>
                            </tr>

                            <tr
                                v-for="asset in warrantyAlerts"
                                :key="asset.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold">
                                    <Link :href="route('assets.show', asset.id)" class="text-neutral-900 hover:underline dark:text-neutral-100">
                                        {{ asset.asset_tag }}
                                    </Link>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ asset.name }}</div>
                                    <div v-if="asset.model_number" class="text-[11px] text-neutral-400">Model: {{ asset.model_number }}</div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 capitalize text-neutral-600 dark:text-neutral-400">
                                    {{ asset.type }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono">
                                    {{ formatDate(asset.warranty_expires_at) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        v-if="asset.is_expired"
                                        class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300"
                                    >
                                        <AlertTriangle class="h-3 w-3" />
                                        Expired
                                    </span>
                                    <span
                                        v-else-if="asset.days_until_expiration !== null && asset.days_until_expiration <= 30"
                                        class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{ asset.days_until_expiration }}d remaining
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-md border border-yellow-200 bg-yellow-50 px-2 py-0.5 text-[11px] font-semibold text-yellow-700 dark:border-yellow-800/60 dark:bg-yellow-950/40 dark:text-yellow-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{ asset.days_until_expiration }}d remaining
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-neutral-600 dark:text-neutral-400">
                                    {{ asset.assignee ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <Button as-child size="sm" variant="ghost" class="h-7 text-xs">
                                        <Link :href="route('assets.show', asset.id)">Inspect</Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: License Alerts Table -->
            <div
                v-else-if="activeTab === 'licenses'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Software Tool</th>
                                <th class="px-4 py-3">Vendor</th>
                                <th class="px-4 py-3">Seats Allocated</th>
                                <th class="px-4 py-3">Billing Cycle</th>
                                <th class="px-4 py-3">Renewal Date</th>
                                <th class="px-4 py-3">Alert Urgency</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="licenseAlerts.length === 0">
                                <td colspan="7" class="py-12 text-center text-neutral-500">
                                    <CheckCircle2 class="mx-auto h-8 w-8 text-emerald-500/70" />
                                    <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">All subscriptions active</p>
                                    <p class="mt-1 text-xs text-neutral-400">No software contracts expiring within the next 60 days.</p>
                                </td>
                            </tr>

                            <tr
                                v-for="lic in licenseAlerts"
                                :key="lic.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="whitespace-nowrap px-4 py-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                    <Link :href="route('licenses.show', lic.id)" class="hover:underline">
                                        {{ lic.name }}
                                    </Link>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-neutral-600 dark:text-neutral-400">
                                    {{ lic.vendor }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono">{{ lic.seats_assigned }} / {{ lic.seats_total }}</td>

                                <td class="whitespace-nowrap px-4 py-3 capitalize text-neutral-600 dark:text-neutral-400">
                                    {{ lic.billing_cycle }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono">
                                    {{ formatDate(lic.expires_at) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        v-if="lic.is_expired"
                                        class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300"
                                    >
                                        <AlertTriangle class="h-3 w-3" />
                                        Expired
                                    </span>
                                    <span
                                        v-else-if="lic.days_until_expiration !== null && lic.days_until_expiration <= 30"
                                        class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{ lic.days_until_expiration }}d remaining
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-md border border-yellow-200 bg-yellow-50 px-2 py-0.5 text-[11px] font-semibold text-yellow-700 dark:border-yellow-800/60 dark:bg-yellow-950/40 dark:text-yellow-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{ lic.days_until_expiration }}d remaining
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <Button as-child size="sm" variant="ghost" class="h-7 text-xs">
                                        <Link :href="route('licenses.show', lic.id)">Manage</Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Resolved History -->
            <div
                v-else-if="activeTab === 'history'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset</th>
                                <th class="px-4 py-3">Repair Summary</th>
                                <th class="px-4 py-3">Provider</th>
                                <th class="px-4 py-3">Completed Date</th>
                                <th class="px-4 py-3">Final Cost</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="recentCompleted.length === 0">
                                <td colspan="6" class="py-12 text-center text-neutral-400">No completed repair records yet.</td>
                            </tr>

                            <tr
                                v-for="item in recentCompleted"
                                :key="item.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold">
                                    <Link
                                        v-if="item.asset"
                                        :href="route('assets.show', item.asset.id)"
                                        class="text-neutral-900 hover:underline dark:text-neutral-100"
                                    >
                                        {{ item.asset.asset_tag }}
                                    </Link>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ item.title }}</div>
                                    <div v-if="item.notes" class="mt-0.5 line-clamp-1 text-[11px] text-neutral-400">{{ item.notes }}</div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-neutral-600 dark:text-neutral-400">
                                    {{ item.provider }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono text-neutral-500">
                                    {{ formatDate(item.completed_at) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 font-mono font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ formatCurrency(item.cost) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium uppercase',
                                            item.status === 'completed'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                                : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <MaintenanceTicketModal v-model:open="ticketModalOpen" :available-assets="availableAssets" />

        <CompleteMaintenanceModal v-model:open="completeModalOpen" :maintenance="activeRepairToComplete" />
    </AppLayout>
</template>
