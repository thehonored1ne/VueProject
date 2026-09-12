<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Asset, TypeOption } from '@/types/asset';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps<{
    asset: Asset;
    types: TypeOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: '/assets',
    },
    {
        title: props.asset.asset_tag,
        href: route('assets.show', props.asset.id),
    },
    {
        title: 'Edit',
        href: route('assets.edit', props.asset.id),
    },
];

const form = useForm({
    name: props.asset.name,
    type: props.asset.type,
    asset_tag: props.asset.asset_tag,
    serial_number: props.asset.serial_number ?? '',
    model_number: props.asset.model_number ?? '',
    cost: props.asset.cost ? String(props.asset.cost) : '',
    purchased_at: props.asset.purchased_at ?? '',
    warranty_expires_at: props.asset.warranty_expires_at ?? '',
    notes: props.asset.notes ?? '',
});

function submit() {
    form.put(route('assets.update', props.asset.id));
}
</script>

<template>
    <Head :title="`Edit ${asset.asset_tag} - ${asset.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Edit Asset Details</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">
                        Update specifications and metadata for {{ asset.asset_tag }}.
                    </p>
                </div>

                <Button as-child variant="outline" size="sm">
                    <Link :href="route('assets.show', asset.id)">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </div>

            <!-- Edit Form Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Hardware Information -->
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">General Information</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-1.5 sm:col-span-2">
                                <Label for="name">Hardware Name <span class="text-rose-500">*</span></Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Category -->
                            <div class="space-y-1.5">
                                <Label for="type">Device Category <span class="text-rose-500">*</span></Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    required
                                    class="flex h-9 w-full rounded-md border border-neutral-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                                >
                                    <option v-for="t in types" :key="t.value" :value="t.value">
                                        {{ t.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.type" class="text-xs text-rose-500">{{ form.errors.type }}</p>
                            </div>

                            <!-- Asset Tag -->
                            <div class="space-y-1.5">
                                <Label for="asset_tag">Asset Tag</Label>
                                <Input id="asset_tag" v-model="form.asset_tag" />
                                <p v-if="form.errors.asset_tag" class="text-xs text-rose-500">{{ form.errors.asset_tag }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Serial & Manufacturer Specs -->
                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Manufacturer Identifiers</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="serial_number">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" />
                                <p v-if="form.errors.serial_number" class="text-xs text-rose-500">{{ form.errors.serial_number }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="model_number">Model Number</Label>
                                <Input id="model_number" v-model="form.model_number" />
                                <p v-if="form.errors.model_number" class="text-xs text-rose-500">{{ form.errors.model_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Procurement & Warranty -->
                    <div class="border-t border-neutral-100 pt-6 dark:border-neutral-800">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Procurement & Warranty</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <Label for="cost">Purchase Cost ($)</Label>
                                <Input id="cost" type="number" step="0.01" v-model="form.cost" />
                                <p v-if="form.errors.cost" class="text-xs text-rose-500">{{ form.errors.cost }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="purchased_at">Purchase Date</Label>
                                <Input id="purchased_at" type="date" v-model="form.purchased_at" />
                                <p v-if="form.errors.purchased_at" class="text-xs text-rose-500">{{ form.errors.purchased_at }}</p>
                            </div>

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
                                class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                            />
                            <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <Button as-child type="button" variant="outline">
                            <Link :href="route('assets.show', asset.id)">Cancel</Link>
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
