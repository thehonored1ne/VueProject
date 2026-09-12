<script setup lang="ts">
import type { AssetMetrics } from '@/types/asset';
import { CheckCircle2, Cpu, UserCheck, Wrench } from 'lucide-vue-next';

defineProps<{
    metrics: AssetMetrics;
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <!-- Total Assets -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Assets</span>
                <div class="rounded-md bg-neutral-100 p-1.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                    <Cpu class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                {{ metrics.total }}
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Registered in catalog</p>
        </div>

        <!-- Deployed / Assigned -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Deployed</span>
                <div class="rounded-md bg-blue-50 p-1.5 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <UserCheck class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">{{ metrics.assigned }}</span>
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ metrics.utilization_rate }}% active</span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                <div class="h-full bg-blue-600 transition-all dark:bg-blue-500" :style="{ width: `${metrics.utilization_rate}%` }" />
            </div>
        </div>

        <!-- Available In Stock -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">In Stock</span>
                <div class="rounded-md bg-emerald-50 p-1.5 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <CheckCircle2 class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                {{ metrics.available }}
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Ready for deployment</p>
        </div>

        <!-- Maintenance -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Maintenance</span>
                <div class="rounded-md bg-amber-50 p-1.5 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <Wrench class="h-4 w-4" />
                </div>
            </div>
            <div class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                {{ metrics.maintenance }}
            </div>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Under inspection or repair</p>
        </div>
    </div>
</template>
