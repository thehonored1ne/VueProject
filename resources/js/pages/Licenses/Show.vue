<script setup lang="ts">
import AssignSeatModal from '@/components/licenses/AssignSeatModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { UserSummary } from '@/types/asset';
import type { SoftwareLicense } from '@/types/license';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Key, Plus, Trash2, UserMinus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    license: SoftwareLicense;
    availableUsers: UserSummary[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Licenses',
        href: '/licenses',
    },
    {
        title: props.license.name,
        href: route('licenses.show', props.license.id),
    },
];

const assignModalOpen = ref(false);

const assignedCount = computed(() => props.license.assignments?.length ?? 0);
const availableSeats = computed(() => Math.max(0, props.license.seats_total - assignedCount.value));
const isFull = computed(() => assignedCount.value >= props.license.seats_total);

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return 'Perpetual';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatCurrency(val?: string | number | null): string {
    if (val === null || val === undefined || val === '') return '—';
    const num = Number(val);
    return isNaN(num) ? '—' : `$${num.toFixed(2)}`;
}

function revokeSeat(userId: number, userName: string) {
    if (confirm(`Revoke seat from ${userName}? This will free up 1 seat on ${props.license.name}.`)) {
        router.delete(route('licenses.revoke', [props.license.id, userId]));
    }
}

function deleteLicense() {
    if (confirm(`Are you sure you want to delete ${props.license.name}?`)) {
        router.delete(route('licenses.destroy', props.license.id));
    }
}
</script>

<template>
    <Head :title="`${license.name} - License Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header Card -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                    >
                        <Key class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ license.name }}
                            </h1>
                            <span
                                :class="[
                                    'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium',
                                    isFull
                                        ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300',
                                ]"
                            >
                                {{ isFull ? 'At Capacity' : `${availableSeats} Seats Available` }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                            Publisher: <strong class="text-neutral-800 dark:text-neutral-200">{{ license.vendor }}</strong>
                            <span v-if="license.license_key">
                                • Key ID: <code class="font-mono">{{ license.license_key }}</code></span
                            >
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        v-if="!isFull"
                        class="bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500"
                        @click="assignModalOpen = true"
                    >
                        <Plus class="mr-1.5 h-4 w-4" />
                        Allocate Seat
                    </Button>

                    <Button as-child variant="outline">
                        <Link :href="route('licenses.edit', license.id)">
                            <Edit class="mr-1.5 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>

                    <Button
                        v-if="assignedCount === 0"
                        variant="outline"
                        class="border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-400 dark:hover:bg-rose-950/40"
                        @click="deleteLicense"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Content: 2-Column Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column: Specs -->
                <div class="space-y-6 lg:col-span-1">
                    <!-- Seat Utilization Meter -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Seat Capacity</h3>

                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ assignedCount }} <span class="text-base font-normal text-neutral-400">/ {{ license.seats_total }} seats</span>
                            </span>
                            <span class="text-xs font-semibold text-neutral-500">
                                {{ Math.round((assignedCount / license.seats_total) * 100) }}% allocated
                            </span>
                        </div>

                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                            <div
                                :class="['h-full transition-all', isFull ? 'bg-rose-500' : 'bg-blue-600 dark:bg-blue-500']"
                                :style="{ width: `${Math.min(100, Math.round((assignedCount / license.seats_total) * 100))}%` }"
                            />
                        </div>

                        <p class="mt-3 text-xs text-neutral-500 dark:text-neutral-400">{{ availableSeats }} seats currently free for team members.</p>
                    </div>

                    <!-- Contract & Billing Details -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Contract & Billing</h3>

                        <dl class="mt-4 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                                <dt class="text-neutral-500 dark:text-neutral-400">Billing Cycle</dt>
                                <dd class="font-medium capitalize text-neutral-900 dark:text-neutral-100">{{ license.billing_cycle }}</dd>
                            </div>

                            <div class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                                <dt class="text-neutral-500 dark:text-neutral-400">Cost Per Seat</dt>
                                <dd class="font-mono font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ formatCurrency(license.cost_per_seat) }}
                                </dd>
                            </div>

                            <div
                                v-if="license.cost_per_seat"
                                class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800"
                            >
                                <dt class="text-neutral-500 dark:text-neutral-400">Total Subscription Cost</dt>
                                <dd class="font-mono font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ formatCurrency(Number(license.cost_per_seat) * license.seats_total) }}
                                </dd>
                            </div>

                            <div class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                                <dt class="text-neutral-500 dark:text-neutral-400">Renewal / Expiration</dt>
                                <dd class="font-medium text-neutral-900 dark:text-neutral-100">{{ formatDate(license.expires_at) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Operational Notes</h3>
                        <div
                            v-if="license.notes"
                            class="mt-2 rounded-lg bg-neutral-50 p-3 text-xs leading-relaxed text-neutral-700 dark:bg-neutral-800/50 dark:text-neutral-300"
                        >
                            {{ license.notes }}
                        </div>
                        <p v-else class="mt-2 text-xs italic text-neutral-400">No notes recorded for this license.</p>
                    </div>
                </div>

                <!-- Right Column: Assigned Team Members -->
                <div class="space-y-4 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Assigned Team Members ({{ assignedCount }})</h3>

                        <Button v-if="!isFull" size="sm" variant="outline" class="h-8 text-xs" @click="assignModalOpen = true">
                            <Plus class="mr-1 h-3.5 w-3.5" />
                            Allocate Seat
                        </Button>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="!license.assignments || license.assignments.length === 0"
                        class="rounded-xl border border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800"
                    >
                        <Users class="mx-auto h-8 w-8 text-neutral-400" />
                        <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No Seats Allocated</p>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Assign your first team member to utilize this subscription.</p>
                        <Button size="sm" class="mt-4 text-xs" @click="assignModalOpen = true"> Allocate Seat </Button>
                    </div>

                    <!-- Member Cards List -->
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="assignment in license.assignments"
                            :key="assignment.id"
                            class="flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                                >
                                    {{ assignment.user?.name.charAt(0) ?? 'U' }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-neutral-100">
                                        {{ assignment.user?.name }}
                                    </h4>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                        {{ assignment.user?.email }}
                                    </p>
                                    <p v-if="assignment.notes" class="mt-0.5 text-[11px] italic text-neutral-400">"{{ assignment.notes }}"</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-neutral-400"> Assigned {{ formatDate(assignment.assigned_at) }} </span>

                                <Button
                                    v-if="assignment.user"
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/30"
                                    @click="revokeSeat(assignment.user.id, assignment.user.name)"
                                >
                                    <UserMinus class="mr-1 h-3 w-3" />
                                    Revoke
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AssignSeatModal v-model:open="assignModalOpen" :license="license" :users="availableUsers" />
    </AppLayout>
</template>
