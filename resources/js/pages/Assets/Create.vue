<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { StatusOption, TypeOption } from '@/types/asset';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

defineProps<{
    types: TypeOption[];
    statuses: StatusOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: '/assets',
    },
    {
        title: 'Register Asset',
        href: '/assets/create',
    },
];

const form = useForm({
    name: '',
    type: 'laptop',
    asset_tag: '',
    serial_number: '',
    model_number: '',
    cost: '',
    purchased_at: new Date().toISOString().split('T')[0],
    warranty_expires_at: '',
    notes: '',
});

function submit() {
    form.post(route('assets.store'));
}
</script>

<template>
    <Head title="Register Hardware Asset" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Register New Asset</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Add a hardware device to your organization's IT inventory.
                    </p>
                </div>

                <Button as-child variant="outline" size="sm">
                    <Link :href="route('assets.index')">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </div>

            <!-- Registration Form Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Hardware Information -->
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">General Information</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-1.5 sm:col-span-2">
                                <Label for="name">Hardware Name <span class="text-rose-500">*</span></Label>
                                <Input id="name" v-model="form.name" placeholder='e.g. MacBook Pro 16" M3 Max (64GB)' required />
                                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Category -->
                            <div class="space-y-1.5">
                                <Label for="type">Device Category <span class="text-rose-500">*</span></Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    required
                                    class="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm text-neutral-900 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus-visible:ring-neutral-300"
                                >
                                    <option v-for="t in types" :key="t.value" :value="t.value">
                                        {{ t.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.type" class="text-xs text-rose-500">{{ form.errors.type }}</p>
                            </div>

                            <!-- Custom Asset Tag -->
                            <div class="space-y-1.5">
                                <Label for="asset_tag">
                                    Asset Tag <span class="text-xs font-normal text-neutral-400">(Leave blank to auto-generate)</span>
                                </Label>
                                <Input id="asset_tag" v-model="form.asset_tag" placeholder="e.g. AST-10082" />
                                <p v-if="form.errors.asset_tag" class="text-xs text-rose-500">{{ form.errors.asset_tag }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Serial & Manufacturer Specs -->
                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Manufacturer Identifiers</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Serial Number -->
                            <div class="space-y-1.5">
                                <Label for="serial_number">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" placeholder="e.g. C02G8790MD6R" />
                                <p v-if="form.errors.serial_number" class="text-xs text-rose-500">{{ form.errors.serial_number }}</p>
                            </div>

                            <!-- Model Number -->
                            <div class="space-y-1.5">
                                <Label for="model_number">Model Number</Label>
                                <Input id="model_number" v-model="form.model_number" placeholder="e.g. A2991" />
                                <p v-if="form.errors.model_number" class="text-xs text-rose-500">{{ form.errors.model_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financials & Warranty -->
                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Procurement & Warranty</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <!-- Purchase Cost -->
                            <div class="space-y-1.5">
                                <Label for="cost">Purchase Cost ($)</Label>
                                <Input id="cost" type="number" step="0.01" v-model="form.cost" placeholder="0.00" />
                                <p v-if="form.errors.cost" class="text-xs text-rose-500">{{ form.errors.cost }}</p>
                            </div>

                            <!-- Purchase Date -->
                            <div class="space-y-1.5">
                                <Label for="purchased_at">Purchase Date</Label>
                                <Input id="purchased_at" type="date" v-model="form.purchased_at" />
                                <p v-if="form.errors.purchased_at" class="text-xs text-rose-500">{{ form.errors.purchased_at }}</p>
                            </div>

                            <!-- Warranty Expiration -->
                            <div class="space-y-1.5">
                                <Label for="warranty_expires_at">Warranty Expiration</Label>
                                <Input id="warranty_expires_at" type="date" v-model="form.warranty_expires_at" />
                                <p v-if="form.errors.warranty_expires_at" class="text-xs text-rose-500">{{ form.errors.warranty_expires_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <div class="space-y-1.5">
                            <Label for="notes">Notes & Operational Configuration</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="MDM profile, BIOS password status, department allocation..."
                                class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                            />
                            <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <Button as-child type="button" variant="outline">
                            <Link :href="route('assets.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-1.5 h-4 w-4" />
                            Register Asset
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
