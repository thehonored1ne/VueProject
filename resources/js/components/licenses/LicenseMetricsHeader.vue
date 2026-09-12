<script setup lang="ts">
import type { LicenseMetrics } from '@/types/license';
import { AlertTriangle, CheckCircle2, Key, Users } from 'lucide-vue-next';

defineProps<{
    metrics: LicenseMetrics;
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <!-- Total Licenses -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Subscriptions</span>
                <div class="rounded-md bg-neutral-100 p-1.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                    <Key class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                {{ metrics.total_licenses }}
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Active software contracts</p>
        </div>

        <!-- Allocated Seats -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Allocated Seats</span>
                <div class="rounded-md bg-blue-50 p-1.5 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <Users class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.allocated_seats }}
                </span>
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                    / {{ metrics.total_seats }} total ({{ metrics.utilization_rate }}%)
                </span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                <div class="h-full bg-blue-600 transition-all dark:bg-blue-500" :style="{ width: `${metrics.utilization_rate}%` }" />
            </div>
        </div>

        <!-- Available Seats -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Available Seats</span>
                <div class="rounded-md bg-emerald-50 p-1.5 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <CheckCircle2 class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                {{ metrics.available_seats }}
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Ready for team allocation</p>
        </div>

        <!-- Expiring Soon -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Renewals Due</span>
                <div
                    :class="[
                        'rounded-md p-1.5',
                        metrics.expiring_count > 0
                            ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                            : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                    ]"
                >
                    <AlertTriangle class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.expiring_count }}
                </span>
                <span v-if="metrics.expiring_count > 0" class="text-xs font-medium text-amber-600 dark:text-amber-400"> Expiring within 30d </span>
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                {{ metrics.expiring_count === 0 ? 'All subscriptions up to date' : 'Attention required' }}
            </p>
        </div>
    </div>
</template>
