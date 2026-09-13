<script setup lang="ts">
import type { DashboardUrgentItem } from '@/types/dashboard';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, ArrowUpRight, CheckCircle2, Clock, KeyRound, ShieldAlert, Wrench } from 'lucide-vue-next';

defineProps<{
    items: DashboardUrgentItem[];
}>();
</script>

<template>
    <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3.5 dark:border-neutral-800 sm:px-6">
            <div class="flex items-center gap-2">
                <AlertCircle class="h-4 w-4 text-rose-500 dark:text-rose-400" />
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Immediate Attention Queue</h2>
                <span
                    v-if="items.length > 0"
                    class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700 dark:bg-rose-950/50 dark:text-rose-300"
                >
                    {{ items.length }}
                </span>
            </div>
            <Link
                :href="route('maintenance.index')"
                class="text-xs font-medium text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200"
            >
                View Maintenance Hub &rarr;
            </Link>
        </div>

        <!-- Content -->
        <div v-if="items.length === 0" class="flex flex-col items-center justify-center p-8 text-center sm:p-12">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
            >
                <CheckCircle2 class="h-5 w-5" />
            </div>
            <h3 class="mt-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">All Operations Nominal</h3>
            <p class="mt-1 max-w-sm text-xs text-neutral-500 dark:text-neutral-400">
                No overdue equipment returns, impending warranty lapses, or critical contract renewals requiring triage.
            </p>
        </div>

        <div v-else class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex flex-col gap-2 p-3.5 transition hover:bg-neutral-50/60 dark:hover:bg-neutral-800/40 sm:flex-row sm:items-center sm:justify-between sm:px-6"
            >
                <div class="flex items-start gap-3">
                    <div
                        :class="[
                            'mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg',
                            item.type === 'overdue_return'
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400'
                                : item.type === 'warranty_expiring'
                                  ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                                  : item.type === 'license_expiring'
                                    ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400'
                                    : 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400',
                        ]"
                    >
                        <Clock v-if="item.type === 'overdue_return'" class="h-3.5 w-3.5" />
                        <ShieldAlert v-else-if="item.type === 'warranty_expiring'" class="h-3.5 w-3.5" />
                        <KeyRound v-else-if="item.type === 'license_expiring'" class="h-3.5 w-3.5" />
                        <Wrench v-else class="h-3.5 w-3.5" />
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ item.title }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wider',
                                    item.urgency === 'critical'
                                        ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'
                                        : item.urgency === 'warning'
                                          ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                                          : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
                                ]"
                            >
                                {{ item.date_label }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                            {{ item.subtitle }}
                        </p>
                    </div>
                </div>

                <div class="mt-1 flex items-center justify-end sm:mt-0">
                    <Link
                        :href="item.action_url"
                        class="inline-flex items-center gap-1 rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-neutral-700 shadow-sm transition hover:bg-neutral-50 hover:text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
                    >
                        <span>{{ item.action_label }}</span>
                        <ArrowUpRight class="h-3 w-3" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
