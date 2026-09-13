<script setup lang="ts">
import CategoryBreakdownCard from '@/components/dashboard/CategoryBreakdownCard.vue';
import DashboardMetricsCards from '@/components/dashboard/DashboardMetricsCards.vue';
import DashboardQuickActions from '@/components/dashboard/DashboardQuickActions.vue';
import RecentActivityFeed from '@/components/dashboard/RecentActivityFeed.vue';
import UrgentAttentionQueue from '@/components/dashboard/UrgentAttentionQueue.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, DashboardActivity, DashboardCategoryStat, DashboardMetrics, DashboardUrgentItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Download, Laptop, Plus } from 'lucide-vue-next';

defineProps<{
    metrics: DashboardMetrics;
    categoryBreakdown: DashboardCategoryStat[];
    urgentItems: DashboardUrgentItem[];
    recentActivity: DashboardActivity[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Executive Operations Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Executive Operations Center</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Capital hardware valuation, active fleet utilization, and operational risk triage.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="route('assets.export')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-medium text-neutral-700 shadow-sm transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
                    >
                        <Download class="h-3.5 w-3.5 text-neutral-500" />
                        <span>Export CSV</span>
                    </a>

                    <Link :href="route('assets.index')">
                        <Button variant="outline" class="gap-1.5 text-xs">
                            <Laptop class="h-3.5 w-3.5" />
                            Hardware Fleet
                        </Button>
                    </Link>

                    <Link :href="route('assets.create')">
                        <Button class="gap-1.5 text-xs">
                            <Plus class="h-3.5 w-3.5" />
                            Register Asset
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Executive KPI Cards -->
            <DashboardMetricsCards :metrics="metrics" />

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left 7 cols: Urgent Queue & Category Composition -->
                <div class="space-y-6 lg:col-span-7">
                    <UrgentAttentionQueue :items="urgentItems" />
                    <CategoryBreakdownCard :categories="categoryBreakdown" :total-assets="metrics.total_asset_count" />
                </div>

                <!-- Right 5 cols: Quick Actions & Operational Audit Stream -->
                <div class="space-y-6 lg:col-span-5">
                    <DashboardQuickActions />
                    <RecentActivityFeed :activities="recentActivity" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
