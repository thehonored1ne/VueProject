<script setup lang="ts">
import OffboardEmployeeModal from '@/components/employees/OffboardEmployeeModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Employee, EmployeeAssetAssignment, EmployeeCustodySummary, EmployeeLicenseAssignment } from '@/types/employee';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle2, Clock, Edit, ExternalLink, History, Key, Laptop, Trash2, UserMinus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    employee: Employee;
    activeHardware: EmployeeAssetAssignment[];
    softwareLicenses: EmployeeLicenseAssignment[];
    custodyHistory: EmployeeAssetAssignment[];
    summary: EmployeeCustodySummary;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
    {
        title: props.employee.name,
        href: route('employees.show', props.employee.id),
    },
];

const activeTab = ref<'hardware' | 'licenses' | 'history'>('hardware');
const offboardModalOpen = ref(false);

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

function isOverdue(returnDateStr?: string | null): boolean {
    if (!returnDateStr) return false;
    const returnDate = new Date(returnDateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return returnDate < today;
}

const hasActiveCustody = computed(() => {
    return props.summary.active_hardware_count > 0 || props.summary.software_seats_count > 0;
});

function deleteEmployee() {
    if (confirm(`Are you sure you want to delete ${props.employee.name}? This action cannot be undone.`)) {
        router.delete(route('employees.destroy', props.employee.id));
    }
}
</script>

<template>
    <Head :title="`${employee.name} - Custody 360° Portal`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Back Button & Breadcrumb Context -->
            <div class="flex items-center gap-2">
                <Button as-child variant="ghost" size="sm" class="h-8 gap-1.5 text-xs text-neutral-600 dark:text-neutral-400">
                    <Link :href="route('employees.index')">
                        <ArrowLeft class="h-3.5 w-3.5" />
                        Back to Employee Directory
                    </Link>
                </Button>
            </div>

            <!-- Employee Profile Header Card -->
            <div
                class="flex flex-col gap-5 rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-100 text-base font-bold text-neutral-800 shadow-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                    >
                        {{ getInitials(employee.name) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ employee.name }}
                            </h1>
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[11px] font-medium',
                                    hasActiveCustody
                                        ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300'
                                        : 'border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400',
                                ]"
                            >
                                {{ hasActiveCustody ? 'Active Custody' : 'No Assets Held' }}
                            </span>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ employee.email }}</p>
                        <p class="mt-1 text-[11px] text-neutral-400 dark:text-neutral-500">Member since {{ formatDate(employee.created_at) }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2.5">
                    <Button as-child variant="outline" size="sm" class="gap-1.5 text-xs shadow-sm">
                        <Link :href="route('employees.edit', employee.id)">
                            <Edit class="h-3.5 w-3.5" />
                            Edit Profile
                        </Link>
                    </Button>

                    <Button variant="destructive" size="sm" class="gap-1.5 text-xs shadow-sm" @click="offboardModalOpen = true">
                        <UserMinus class="h-3.5 w-3.5" />
                        Offboard Employee
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        class="gap-1.5 border-rose-200 text-xs text-rose-600 shadow-sm hover:bg-rose-50 hover:text-rose-700 dark:border-rose-900/60 dark:text-rose-400 dark:hover:bg-rose-950/40"
                        @click="deleteEmployee"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Custody Metrics Summary Row -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Active Hardware Devices</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                        >
                            <Laptop class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ summary.active_hardware_count }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Devices currently checked out</p>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Software License Seats</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400"
                        >
                            <Key class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ summary.software_seats_count }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Active software seats allocated</p>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Lifetime Assignments</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                        >
                            <History class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900 dark:text-neutral-50">
                        {{ summary.lifetime_hardware_count }}
                    </div>
                    <p class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">Total historical checkout events</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-neutral-200 dark:border-neutral-800">
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-medium transition-colors',
                        activeTab === 'hardware'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200',
                    ]"
                    @click="activeTab = 'hardware'"
                >
                    <Laptop class="h-3.5 w-3.5" />
                    Active Hardware ({{ activeHardware.length }})
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-medium transition-colors',
                        activeTab === 'licenses'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200',
                    ]"
                    @click="activeTab = 'licenses'"
                >
                    <Key class="h-3.5 w-3.5" />
                    Software Licenses ({{ softwareLicenses.length }})
                </button>

                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-medium transition-colors',
                        activeTab === 'history'
                            ? 'border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
                            : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200',
                    ]"
                    @click="activeTab = 'history'"
                >
                    <History class="h-3.5 w-3.5" />
                    Custody Audit History ({{ custodyHistory.length }})
                </button>
            </div>

            <!-- Tab 1: Active Hardware -->
            <div
                v-show="activeTab === 'hardware'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset Tag & Device</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Serial / Model</th>
                                <th class="px-4 py-3">Checked Out</th>
                                <th class="px-4 py-3">Expected Return</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="activeHardware.length === 0">
                                <td colspan="7" class="py-10 text-center">
                                    <Laptop class="mx-auto h-7 w-7 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-xs font-medium text-neutral-900 dark:text-neutral-100">
                                        No physical hardware currently checked out
                                    </p>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Assign equipment from the Assets catalog.</p>
                                </td>
                            </tr>

                            <tr
                                v-for="item in activeHardware"
                                :key="item.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                        >
                                            {{ item.asset.asset_tag }}
                                        </span>
                                        <Link
                                            :href="route('assets.show', item.asset.id)"
                                            class="font-semibold text-neutral-900 hover:underline dark:text-neutral-100"
                                        >
                                            {{ item.asset.name }}
                                        </Link>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="capitalize text-neutral-600 dark:text-neutral-400">
                                        {{ item.asset.type }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 font-mono text-[11px] text-neutral-600 dark:text-neutral-400">
                                    {{ item.asset.serial_number || '—' }}
                                </td>

                                <td class="px-4 py-3 tabular-nums text-neutral-600 dark:text-neutral-400">
                                    {{ formatDate(item.assigned_at) }}
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        v-if="item.expected_return_at"
                                        :class="[
                                            'inline-flex items-center gap-1 rounded border px-1.5 py-0.5 text-[11px] font-medium',
                                            isOverdue(item.expected_return_at)
                                                ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300'
                                                : 'border-neutral-200 bg-neutral-50 text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300',
                                        ]"
                                    >
                                        <AlertCircle v-if="isOverdue(item.expected_return_at)" class="h-3 w-3" />
                                        {{ formatDate(item.expected_return_at) }}
                                        <span v-if="isOverdue(item.expected_return_at)">(Overdue)</span>
                                    </span>
                                    <span v-else class="text-[11px] text-neutral-400 dark:text-neutral-500"> Indefinite </span>
                                </td>

                                <td class="px-4 py-3 text-[11px] text-neutral-600 dark:text-neutral-400">
                                    {{ item.condition_on_assignment || 'Good working order' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <Button as-child variant="ghost" size="sm" class="h-7 gap-1 text-xs">
                                        <Link :href="route('assets.show', item.asset.id)">
                                            <span>Details</span>
                                            <ExternalLink class="h-3 w-3" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Software Licenses -->
            <div
                v-show="activeTab === 'licenses'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Software & Vendor</th>
                                <th class="px-4 py-3">License Key / Subscription ID</th>
                                <th class="px-4 py-3">Billing Cycle</th>
                                <th class="px-4 py-3">Seat Assigned</th>
                                <th class="px-4 py-3">Notes</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="softwareLicenses.length === 0">
                                <td colspan="6" class="py-10 text-center">
                                    <Key class="mx-auto h-7 w-7 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-xs font-medium text-neutral-900 dark:text-neutral-100">No software licenses assigned</p>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Allocate license seats from the Licenses module.</p>
                                </td>
                            </tr>

                            <tr
                                v-for="item in softwareLicenses"
                                :key="item.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="px-4 py-3">
                                    <div>
                                        <Link
                                            :href="route('licenses.show', item.software_license_id)"
                                            class="font-semibold text-neutral-900 hover:underline dark:text-neutral-100"
                                        >
                                            {{ item.license.name }}
                                        </Link>
                                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ item.license.vendor }}</p>
                                    </div>
                                </td>

                                <td class="px-4 py-3 font-mono text-[11px] text-neutral-600 dark:text-neutral-400">
                                    {{ item.license.license_key || 'Subscription / Cloud' }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="capitalize text-neutral-600 dark:text-neutral-400">
                                        {{ item.license.billing_cycle }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 tabular-nums text-neutral-600 dark:text-neutral-400">
                                    {{ formatDate(item.assigned_at) }}
                                </td>

                                <td class="max-w-xs truncate px-4 py-3 text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ item.notes || '—' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <Button as-child variant="ghost" size="sm" class="h-7 gap-1 text-xs">
                                        <Link :href="route('licenses.show', item.software_license_id)">
                                            <span>Manage License</span>
                                            <ExternalLink class="h-3 w-3" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Custody Audit History -->
            <div
                v-show="activeTab === 'history'"
                class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-neutral-200 bg-neutral-50/75 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Asset</th>
                                <th class="px-4 py-3">Custody Status</th>
                                <th class="px-4 py-3">Issued Date</th>
                                <th class="px-4 py-3">Returned Date</th>
                                <th class="px-4 py-3">Condition on Return / Notes</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                            <tr v-if="custodyHistory.length === 0">
                                <td colspan="5" class="py-10 text-center">
                                    <History class="mx-auto h-7 w-7 text-neutral-300 dark:text-neutral-600" />
                                    <p class="mt-2 text-xs font-medium text-neutral-900 dark:text-neutral-100">No historical checkout records</p>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                        Past and present assignments will be audited here.
                                    </p>
                                </td>
                            </tr>

                            <tr
                                v-for="history in custodyHistory"
                                :key="history.id"
                                class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                        >
                                            {{ history.asset.asset_tag }}
                                        </span>
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ history.asset.name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        v-if="history.returned_at"
                                        class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                                    >
                                        <CheckCircle2 class="h-3 w-3" />
                                        Returned
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        In Custody
                                    </span>
                                </td>

                                <td class="px-4 py-3 tabular-nums text-neutral-600 dark:text-neutral-400">
                                    {{ formatDate(history.assigned_at) }}
                                </td>

                                <td class="px-4 py-3 tabular-nums text-neutral-600 dark:text-neutral-400">
                                    {{ history.returned_at ? formatDate(history.returned_at) : '—' }}
                                </td>

                                <td class="max-w-sm px-4 py-3 text-[11px] text-neutral-600 dark:text-neutral-400">
                                    <div v-if="history.condition_on_return" class="font-medium text-neutral-700 dark:text-neutral-300">
                                        {{ history.condition_on_return }}
                                    </div>
                                    <div v-if="history.notes" class="mt-0.5 whitespace-pre-line text-[10px] text-neutral-500 dark:text-neutral-400">
                                        {{ history.notes }}
                                    </div>
                                    <div v-if="!history.condition_on_return && !history.notes" class="text-neutral-400">—</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Offboard Confirmation Modal -->
            <OffboardEmployeeModal
                :employee="employee"
                :open="offboardModalOpen"
                :active-hardware-count="summary.active_hardware_count"
                :active-licenses-count="summary.software_seats_count"
                @update:open="offboardModalOpen = $event"
            />
        </div>
    </AppLayout>
</template>
