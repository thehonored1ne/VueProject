<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { BillingCycleOption, SoftwareLicense } from '@/types/license';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps<{
    license: SoftwareLicense;
    billingCycles: BillingCycleOption[];
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
    {
        title: 'Edit',
        href: route('licenses.edit', props.license.id),
    },
];

const form = useForm({
    name: props.license.name,
    vendor: props.license.vendor,
    seats_total: props.license.seats_total,
    billing_cycle: props.license.billing_cycle,
    cost_per_seat: props.license.cost_per_seat ? String(props.license.cost_per_seat) : '',
    license_key: props.license.license_key ?? '',
    expires_at: props.license.expires_at ?? '',
    notes: props.license.notes ?? '',
});

function submit() {
    form.put(route('licenses.update', props.license.id));
}
</script>

<template>
    <Head :title="`Edit ${license.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Edit License Details</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Update subscription terms, total seat capacity, and contract info for {{ license.name }}.
                    </p>
                </div>

                <Button as-child variant="outline" size="sm">
                    <Link :href="route('licenses.show', license.id)">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </div>

            <!-- Form Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Software & Vendor</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5 sm:col-span-2">
                                <Label for="name">Software Name <span class="text-rose-500">*</span></Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="vendor">Publisher / Vendor <span class="text-rose-500">*</span></Label>
                                <Input id="vendor" v-model="form.vendor" required />
                                <p v-if="form.errors.vendor" class="text-xs text-rose-500">{{ form.errors.vendor }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="license_key">License Key / Account ID</Label>
                                <Input id="license_key" v-model="form.license_key" />
                                <p v-if="form.errors.license_key" class="text-xs text-rose-500">{{ form.errors.license_key }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Seat Capacity & Billing</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <Label for="seats_total">Total Purchased Seats <span class="text-rose-500">*</span></Label>
                                <Input id="seats_total" type="number" min="1" v-model="form.seats_total" required />
                                <p v-if="form.errors.seats_total" class="text-xs text-rose-500">{{ form.errors.seats_total }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="billing_cycle">Billing Cycle <span class="text-rose-500">*</span></Label>
                                <select
                                    id="billing_cycle"
                                    v-model="form.billing_cycle"
                                    required
                                    class="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm text-neutral-900 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus-visible:ring-neutral-300"
                                >
                                    <option v-for="cycle in billingCycles" :key="cycle.value" :value="cycle.value">
                                        {{ cycle.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.billing_cycle" class="text-xs text-rose-500">{{ form.errors.billing_cycle }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="cost_per_seat">Cost Per Seat ($)</Label>
                                <Input id="cost_per_seat" type="number" step="0.01" v-model="form.cost_per_seat" />
                                <p v-if="form.errors.cost_per_seat" class="text-xs text-rose-500">{{ form.errors.cost_per_seat }}</p>
                            </div>

                            <div class="space-y-1.5 sm:col-span-2">
                                <Label for="expires_at">Expiration / Renewal Date</Label>
                                <Input id="expires_at" type="date" v-model="form.expires_at" />
                                <p v-if="form.errors.expires_at" class="text-xs text-rose-500">{{ form.errors.expires_at }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <div class="space-y-1.5">
                            <Label for="notes">Notes & Procurement Details</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                            />
                            <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <Button as-child type="button" variant="outline">
                            <Link :href="route('licenses.show', license.id)">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-1.5 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
