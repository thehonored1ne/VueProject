<script setup lang="ts">
import type { MaintenanceMetrics } from '@/types/maintenance';
import { DollarSign, KeyRound, ShieldAlert, Wrench } from 'lucide-vue-next';

defineProps<{
    metrics: MaintenanceMetrics;
}>();

function formatCurrency(val?: number | null): string {
    if (val === null || val === undefined) return '$0.00';
    return `$${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <!-- Active Repairs -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Active Repairs</span>
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <Wrench class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.active_repairs }}
                </span>
                <span class="text-[11px] font-medium text-blue-600 dark:text-blue-400"> In Workshop </span>
            </div>
        </div>

        <!-- Expiring Warranties -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Warranty Alerts</span>
                <div
                    :class="[
                        'flex h-7 w-7 items-center justify-center rounded-lg',
                        metrics.expiring_warranties > 0
                            ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                            : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400',
                    ]"
                >
                    <ShieldAlert class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.expiring_warranties }}
                </span>
                <span class="text-[11px] text-neutral-400"> ≤ 60 days </span>
            </div>
        </div>

        <!-- Expiring Licenses -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">License Renewals</span>
                <div
                    :class="[
                        'flex h-7 w-7 items-center justify-center rounded-lg',
                        metrics.expiring_licenses > 0
                            ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400'
                            : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400',
                    ]"
                >
                    <KeyRound class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.expiring_licenses }}
                </span>
                <span class="text-[11px] text-neutral-400"> ≤ 60 days </span>
            </div>
        </div>

        <!-- Total Servicing Costs -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Repair Costs</span>
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <DollarSign class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-mono text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ formatCurrency(metrics.total_repair_costs) }}
                </span>
                <span class="text-[11px] text-neutral-400"> Resolved </span>
            </div>
        </div>
    </div>
</template>
