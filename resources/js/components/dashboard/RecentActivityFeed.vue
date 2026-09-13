<script setup lang="ts">
import type { DashboardActivity } from '@/types/dashboard';
import { Link } from '@inertiajs/vue3';
import { Activity, ArrowDownLeft, ArrowUpRight, CheckCircle2, KeyRound, Wrench } from 'lucide-vue-next';

defineProps<{
    activities: DashboardActivity[];
}>();

function getActivityIcon(type: string) {
    switch (type) {
        case 'asset_checkout':
            return ArrowUpRight;
        case 'asset_checkin':
            return ArrowDownLeft;
        case 'repair_completed':
            return CheckCircle2;
        case 'repair_logged':
            return Wrench;
        case 'license_allocated':
            return KeyRound;
        default:
            return Activity;
    }
}
</script>

<template>
    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
            <div class="flex items-center gap-2">
                <Activity class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Operational Activity Stream</h2>
            </div>
            <span class="text-[11px] text-neutral-400">Recent events</span>
        </div>

        <div v-if="activities.length === 0" class="py-8 text-center text-xs text-neutral-400">No recent activity recorded.</div>

        <div
            v-else
            class="relative mt-4 space-y-4 before:absolute before:bottom-2 before:left-[13px] before:top-2 before:w-[1.5px] before:bg-neutral-200 dark:before:bg-neutral-800"
        >
            <div v-for="act in activities" :key="act.id" class="relative flex items-start gap-3 text-xs">
                <div
                    :class="[
                        'relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white dark:border-neutral-900',
                        act.type === 'asset_checkout'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400'
                            : act.type === 'asset_checkin'
                              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
                              : act.type === 'repair_completed'
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
                                : act.type === 'repair_logged'
                                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400'
                                  : 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-400',
                    ]"
                >
                    <component :is="getActivityIcon(act.type)" class="h-3.5 w-3.5" />
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <div class="flex items-center justify-between gap-2">
                        <Link
                            :href="act.url"
                            class="font-medium text-neutral-900 transition hover:text-blue-600 dark:text-neutral-100 dark:hover:text-blue-400"
                        >
                            {{ act.title }}
                        </Link>
                        <span class="shrink-0 text-[10px] text-neutral-400">{{ act.time_ago }}</span>
                    </div>
                    <p class="mt-0.5 truncate text-[11px] text-neutral-500 dark:text-neutral-400">
                        {{ act.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
