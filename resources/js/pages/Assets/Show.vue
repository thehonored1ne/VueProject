<script setup lang="ts">
import AssetQrModal from '@/components/assets/AssetQrModal.vue';
import AssetStatusBadge from '@/components/assets/AssetStatusBadge.vue';
import AssetTimeline from '@/components/assets/AssetTimeline.vue';
import AssignAssetModal from '@/components/assets/AssignAssetModal.vue';
import CheckInAssetModal from '@/components/assets/CheckInAssetModal.vue';
import MaintenanceTicketModal from '@/components/maintenance/MaintenanceTicketModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Asset, UserSummary } from '@/types/asset';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle, Clock, Edit, Laptop, LogIn, LogOut, QrCode, Trash2, Wrench } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    asset: Asset;
    users: UserSummary[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: '/assets',
    },
    {
        title: props.asset.asset_tag,
        href: route('assets.show', props.asset.id),
    },
];

const assignModalOpen = ref(false);
const checkInModalOpen = ref(false);
const qrModalOpen = ref(false);
const repairModalOpen = ref(false);

const warrantyStatus = computed(() => {
    if (!props.asset.warranty_expires_at) return null;

    const expires = new Date(props.asset.warranty_expires_at);
    const now = new Date();
    const diffDays = Math.ceil((expires.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return {
            label: 'Warranty Expired',
            badgeClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
            icon: AlertTriangle,
            warning: true,
        };
    } else if (diffDays <= 30) {
        return {
            label: `Expires in ${diffDays} days`,
            badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
            icon: Clock,
            warning: true,
        };
    }

    return {
        label: 'Under Warranty',
        badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
        icon: CheckCircle,
        warning: false,
    };
});

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatCurrency(val?: string | number | null): string {
    if (val === null || val === undefined || val === '') return '—';
    const num = Number(val);
    return isNaN(num) ? '—' : `$${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function deleteAsset() {
    if (confirm(`Are you sure you want to delete asset ${props.asset.asset_tag}? This action cannot be undone.`)) {
        router.delete(route('assets.destroy', props.asset.id));
    }
}
</script>

<template>
    <Head :title="`${asset.asset_tag} - ${asset.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Banner -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200"
                    >
                        <Laptop class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ asset.name }}
                            </h1>
                            <AssetStatusBadge :status="asset.status" />
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-3 font-mono text-xs text-neutral-500 dark:text-neutral-400">
                            <span
                                >Tag: <strong class="text-neutral-800 dark:text-neutral-200">{{ asset.asset_tag }}</strong></span
                            >
                            <span v-if="asset.serial_number"
                                >• Serial: <strong class="text-neutral-800 dark:text-neutral-200">{{ asset.serial_number }}</strong></span
                            >
                            <span v-if="asset.model_number"
                                >• Model: <strong class="text-neutral-800 dark:text-neutral-200">{{ asset.model_number }}</strong></span
                            >
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        v-if="asset.status === 'available'"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                        @click="assignModalOpen = true"
                    >
                        <LogOut class="mr-1.5 h-4 w-4" />
                        Check Out
                    </Button>

                    <Button
                        v-else-if="asset.status === 'assigned'"
                        class="bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500"
                        @click="checkInModalOpen = true"
                    >
                        <LogIn class="mr-1.5 h-4 w-4" />
                        Check In
                    </Button>

                    <Button variant="outline" @click="qrModalOpen = true" class="gap-1.5">
                        <QrCode class="h-4 w-4" />
                        QR Label
                    </Button>

                    <Button v-if="asset.status !== 'maintenance'" variant="outline" class="gap-1.5" @click="repairModalOpen = true">
                        <Wrench class="h-4 w-4" />
                        Log Repair
                    </Button>

                    <Button as-child variant="outline">
                        <Link :href="route('assets.edit', asset.id)">
                            <Edit class="mr-1.5 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>

                    <Button
                        v-if="asset.status !== 'assigned'"
                        variant="outline"
                        class="border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-400 dark:hover:bg-rose-950/40"
                        @click="deleteAsset"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Main Content: 2-Column Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Details (2 cols) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Current Active Custody -->
                    <div
                        v-if="asset.status === 'assigned' && asset.current_assignment"
                        class="rounded-xl border border-blue-200 bg-blue-50/40 p-5 dark:border-blue-900/60 dark:bg-blue-950/20"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                                Active Custody Assignment
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600" />
                                Deployed
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 font-bold text-white">
                                {{ asset.current_assignment.user?.name.charAt(0) ?? 'U' }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ asset.current_assignment.user?.name }}
                                </h4>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ asset.current_assignment.user?.email }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 border-t border-blue-200/60 pt-3 text-xs dark:border-blue-900/40 sm:grid-cols-2">
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Assigned On:</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ formatDate(asset.current_assignment.assigned_at) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Expected Return:</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ formatDate(asset.current_assignment.expected_return_at) }}
                                </div>
                            </div>
                            <div v-if="asset.current_assignment.condition_on_assignment" class="sm:col-span-2">
                                <span class="text-neutral-500 dark:text-neutral-400">Checkout Condition:</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ asset.current_assignment.condition_on_assignment }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hardware Specifications -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Hardware Specifications</h3>

                        <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 text-xs sm:grid-cols-2">
                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Category</dt>
                                <dd class="mt-1 font-medium capitalize text-neutral-900 dark:text-neutral-100">{{ asset.type }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Asset Tag</dt>
                                <dd class="mt-1 font-mono font-semibold text-neutral-900 dark:text-neutral-100">{{ asset.asset_tag }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Serial Number</dt>
                                <dd class="mt-1 font-mono text-neutral-900 dark:text-neutral-100">{{ asset.serial_number ?? '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Model Number</dt>
                                <dd class="mt-1 font-mono text-neutral-900 dark:text-neutral-100">{{ asset.model_number ?? '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Purchase Date</dt>
                                <dd class="mt-1 font-medium text-neutral-900 dark:text-neutral-100">{{ formatDate(asset.purchased_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Purchase Cost</dt>
                                <dd class="mt-1 font-mono font-medium text-neutral-900 dark:text-neutral-100">{{ formatCurrency(asset.cost) }}</dd>
                            </div>

                            <div>
                                <dt class="text-neutral-500 dark:text-neutral-400">Warranty Expiration</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{
                                        formatDate(asset.warranty_expires_at)
                                    }}</span>
                                    <span
                                        v-if="warrantyStatus"
                                        :class="[
                                            'inline-flex items-center gap-1 rounded border px-1.5 py-0.5 text-[10px] font-medium',
                                            warrantyStatus.badgeClass,
                                        ]"
                                    >
                                        <component :is="warrantyStatus.icon" class="h-3 w-3" />
                                        {{ warrantyStatus.label }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Operational Notes -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Operational Notes</h3>
                        <div
                            v-if="asset.notes"
                            class="mt-2 rounded-lg bg-neutral-50 p-3 text-xs leading-relaxed text-neutral-700 dark:bg-neutral-800/50 dark:text-neutral-300"
                        >
                            {{ asset.notes }}
                        </div>
                        <p v-else class="mt-2 text-xs italic text-neutral-400">No notes recorded for this asset.</p>
                    </div>
                </div>

                <!-- Right Column: Timeline (1 col) -->
                <div class="lg:col-span-1">
                    <AssetTimeline :assignments="asset.assignments ?? []" />
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AssignAssetModal v-model:open="assignModalOpen" :asset="asset" :users="users" />

        <CheckInAssetModal v-model:open="checkInModalOpen" :asset="asset" />

        <AssetQrModal v-model:open="qrModalOpen" :asset="asset" />

        <MaintenanceTicketModal v-model:open="repairModalOpen" :asset="asset" />
    </AppLayout>
</template>
