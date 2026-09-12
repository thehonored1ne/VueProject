<script setup lang="ts">
import type { AssetAssignment } from '@/types/asset';
import { Calendar, CheckCircle, Clock, ShieldCheck, User } from 'lucide-vue-next';

defineProps<{
    assignments: AssetAssignment[];
}>();

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Custody Audit Trail</h3>

        <div v-if="assignments.length === 0" class="rounded-xl border border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800">
            <Clock class="mx-auto h-8 w-8 text-neutral-400" />
            <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No Assignment History</p>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">This hardware asset has never been checked out to an employee.</p>
        </div>

        <div
            v-else
            class="relative space-y-6 pl-6 before:absolute before:bottom-0 before:left-2.5 before:top-2 before:w-0.5 before:bg-neutral-200 dark:before:bg-neutral-800"
        >
            <div v-for="item in assignments" :key="item.id" class="group relative">
                <!-- Timeline indicator node -->
                <div
                    :class="[
                        'absolute -left-6 top-1 flex h-5 w-5 items-center justify-center rounded-full ring-4 ring-white dark:ring-neutral-950',
                        !item.returned_at ? 'bg-blue-600 text-white' : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                    ]"
                >
                    <User v-if="!item.returned_at" class="h-3 w-3" />
                    <CheckCircle v-else class="h-3 w-3" />
                </div>

                <!-- Content Card -->
                <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                {{ item.user?.name ?? 'Unknown Team Member' }}
                            </span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400"> ({{ item.user?.email }}) </span>
                        </div>

                        <div>
                            <span
                                v-if="!item.returned_at"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300"
                            >
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-500" />
                                Active Custody
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400"
                            >
                                Returned
                            </span>
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="mt-3 grid grid-cols-1 gap-2 text-xs text-neutral-600 dark:text-neutral-400 sm:grid-cols-2">
                        <div class="flex items-center gap-1.5">
                            <Calendar class="h-3.5 w-3.5 text-neutral-400" />
                            <span
                                >Checked out: <strong>{{ formatDate(item.assigned_at) }}</strong></span
                            >
                        </div>
                        <div class="flex items-center gap-1.5">
                            <Clock class="h-3.5 w-3.5 text-neutral-400" />
                            <span v-if="item.returned_at">
                                Returned: <strong>{{ formatDate(item.returned_at) }}</strong>
                            </span>
                            <span v-else-if="item.expected_return_at">
                                Expected back: <strong>{{ formatDate(item.expected_return_at) }}</strong>
                            </span>
                            <span v-else> Open-ended deployment </span>
                        </div>
                        <div v-if="item.assigned_by_user" class="flex items-center gap-1.5">
                            <ShieldCheck class="h-3.5 w-3.5 text-neutral-400" />
                            <span
                                >Issued by: <strong>{{ item.assigned_by_user.name }}</strong></span
                            >
                        </div>
                    </div>

                    <!-- Conditions & Notes -->
                    <div class="mt-3 space-y-1.5 border-t border-neutral-100 pt-3 text-xs dark:border-neutral-800/80">
                        <div v-if="item.condition_on_assignment">
                            <span class="text-neutral-400">Condition on Checkout:</span>
                            <span class="ml-1 text-neutral-800 dark:text-neutral-200">{{ item.condition_on_assignment }}</span>
                        </div>
                        <div v-if="item.condition_on_return">
                            <span class="text-neutral-400">Condition on Return:</span>
                            <span class="ml-1 text-neutral-800 dark:text-neutral-200">{{ item.condition_on_return }}</span>
                        </div>
                        <div v-if="item.notes" class="rounded bg-neutral-50 p-2 text-neutral-700 dark:bg-neutral-800/40 dark:text-neutral-300">
                            {{ item.notes }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
