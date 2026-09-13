<script setup lang="ts">
import type { DashboardMetrics } from '@/types/dashboard';
import { AlertTriangle, KeyRound, Laptop, ShieldCheck, Wallet } from 'lucide-vue-next';

defineProps<{
    metrics: DashboardMetrics;
}>();

function formatCurrency(val?: number | null): string {
    if (val === null || val === undefined) return '$0.00';
    return `$${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <!-- 1. Fleet Capital Valuation -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Fleet Valuation</span>
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <Wallet class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-mono text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ formatCurrency(metrics.total_fleet_valuation) }}
                </span>
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400">
                <span
                    >Hardware:
                    <strong class="font-medium text-neutral-700 dark:text-neutral-300">{{
                        formatCurrency(metrics.total_hardware_value)
                    }}</strong></span
                >
                <span
                    >Software:
                    <strong class="font-medium text-neutral-700 dark:text-neutral-300">{{ formatCurrency(metrics.annual_software_spend) }}</strong
                    >/yr</span
                >
            </div>
        </div>

        <!-- 2. Hardware Fleet Utilization -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Hardware Deployment</span>
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <Laptop class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-mono text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.hardware_utilization_rate }}%
                </span>
                <span class="text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    {{ metrics.asset_status_counts.assigned }} / {{ metrics.total_asset_count }} Units
                </span>
            </div>
            <!-- Mini progress bar -->
            <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                <div
                    class="h-full rounded-full bg-blue-500 transition-all duration-500"
                    :style="{ width: `${Math.min(100, metrics.hardware_utilization_rate)}%` }"
                />
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400">
                <span>{{ metrics.asset_status_counts.available }} Available</span>
                <span>{{ metrics.asset_status_counts.maintenance }} In Repair</span>
            </div>
        </div>

        <!-- 3. Software Seat Saturation -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Software Seat Saturation</span>
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                >
                    <KeyRound class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-mono text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ metrics.seat_utilization_rate }}%
                </span>
                <span class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    {{ metrics.allocated_software_seats }} / {{ metrics.total_software_seats }} Seats
                </span>
            </div>
            <!-- Mini progress bar -->
            <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                <div
                    class="h-full rounded-full bg-indigo-500 transition-all duration-500"
                    :style="{ width: `${Math.min(100, metrics.seat_utilization_rate)}%` }"
                />
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400">
                <span>{{ metrics.available_software_seats }} Seats Free</span>
                <span>{{ metrics.total_licenses }} Subscriptions</span>
            </div>
        </div>

        <!-- 4. Attention Required -->
        <div
            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Urgent Attention</span>
                <div
                    :class="[
                        'flex h-7 w-7 items-center justify-center rounded-lg',
                        metrics.urgent_total_count > 0
                            ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400'
                            : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
                    ]"
                >
                    <AlertTriangle v-if="metrics.urgent_total_count > 0" class="h-3.5 w-3.5" />
                    <ShieldCheck v-else class="h-3.5 w-3.5" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span
                    :class="[
                        'font-mono text-2xl font-bold tracking-tight',
                        metrics.urgent_total_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-neutral-900 dark:text-neutral-50',
                    ]"
                >
                    {{ metrics.urgent_total_count }}
                </span>
                <span
                    :class="[
                        'text-[11px] font-medium',
                        metrics.urgent_total_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400',
                    ]"
                >
                    {{ metrics.urgent_total_count > 0 ? 'Action Required' : 'All Clear' }}
                </span>
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400">
                <span>{{ metrics.overdue_returns_count }} Overdue</span>
                <span>{{ metrics.expiring_warranties_count }} Warranties</span>
                <span>{{ metrics.active_repairs_count }} Repairs</span>
            </div>
        </div>
    </div>
</template>
