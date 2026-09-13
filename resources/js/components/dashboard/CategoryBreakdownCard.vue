<script setup lang="ts">
import type { DashboardCategoryStat } from '@/types/dashboard';
import { Link } from '@inertiajs/vue3';
import { Cpu, HardDrive, Laptop, Monitor, Smartphone, Tag } from 'lucide-vue-next';

defineProps<{
    categories: DashboardCategoryStat[];
    totalAssets: number;
}>();

function getCategoryIcon(type: string) {
    switch (type) {
        case 'laptop':
            return Laptop;
        case 'desktop':
            return HardDrive;
        case 'monitor':
            return Monitor;
        case 'mobile':
            return Smartphone;
        case 'server':
            return Cpu;
        default:
            return Tag;
    }
}
</script>

<template>
    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
            <div>
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Fleet Inventory Composition</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Hardware assets grouped by equipment category</p>
            </div>
            <Link
                :href="route('assets.index')"
                class="text-xs font-medium text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200"
            >
                View Inventory &rarr;
            </Link>
        </div>

        <div v-if="categories.length === 0" class="py-8 text-center text-xs text-neutral-400">No hardware inventory registered yet.</div>

        <div v-else class="mt-4 space-y-3.5">
            <div v-for="cat in categories" :key="cat.type" class="group rounded-lg p-1.5 transition hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <component :is="getCategoryIcon(cat.type)" class="h-3.5 w-3.5 text-neutral-500 dark:text-neutral-400" />
                        <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ cat.label }}</span>
                        <span class="text-[11px] text-neutral-400">({{ cat.total }} units)</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono text-[11px]">
                        <span class="text-neutral-500 dark:text-neutral-400"> {{ cat.deployed }} deployed ({{ cat.deployment_rate }}%) </span>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ cat.percentage }}%</span>
                    </div>
                </div>

                <!-- Dual-layer visual bar -->
                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                    <div
                        class="h-full rounded-full bg-neutral-800 transition-all duration-500 dark:bg-neutral-200"
                        :style="{ width: `${cat.percentage}%` }"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
