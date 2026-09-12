<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface SimpleAsset {
    id: number;
    asset_tag: string;
    name: string;
    status?: string;
}

const props = defineProps<{
    open: boolean;
    asset?: SimpleAsset | null;
    availableAssets?: SimpleAsset[];
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    asset_id: props.asset?.id ? String(props.asset.id) : '',
    title: '',
    provider: '',
    cost: '',
    status: 'in_progress',
    scheduled_at: '',
    started_at: new Date().toISOString().split('T')[0],
    notes: '',
});

watch(
    () => [props.open, props.asset],
    () => {
        if (props.open) {
            form.asset_id = props.asset?.id ? String(props.asset.id) : '';
            if (!form.started_at) {
                form.started_at = new Date().toISOString().split('T')[0];
            }
        }
    },
);

function handleSubmit() {
    form.post(route('maintenance.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('update:open', false);
            emit('success');
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Log Equipment Repair</DialogTitle>
                <DialogDescription>
                    Schedule or start an equipment repair ticket. The asset status will automatically switch to Maintenance.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Asset Selection -->
                <div class="space-y-1.5">
                    <Label for="maintenance_asset_id">Hardware Asset <span class="text-rose-500">*</span></Label>
                    <div
                        v-if="asset"
                        class="rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2 text-sm font-medium dark:border-neutral-800 dark:bg-neutral-800/50"
                    >
                        <span class="mr-2 font-mono text-xs text-neutral-500">[{{ asset.asset_tag }}]</span>
                        {{ asset.name }}
                    </div>
                    <select
                        v-else
                        id="maintenance_asset_id"
                        v-model="form.asset_id"
                        required
                        class="flex h-9 w-full rounded-md border border-neutral-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    >
                        <option value="" disabled>Select hardware asset to service...</option>
                        <option v-for="a in availableAssets" :key="a.id" :value="String(a.id)">
                            [{{ a.asset_tag }}] {{ a.name }} ({{ a.status }})
                        </option>
                    </select>
                    <InputError :message="form.errors.asset_id" />
                </div>

                <!-- Issue Title -->
                <div class="space-y-1.5">
                    <Label for="title">Issue Summary <span class="text-rose-500">*</span></Label>
                    <Input id="title" v-model="form.title" placeholder="e.g., Cracked display replacement, swollen battery" required />
                    <InputError :message="form.errors.title" />
                </div>

                <!-- Service Provider / Technician -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="provider">Service Provider <span class="text-rose-500">*</span></Label>
                        <Input id="provider" v-model="form.provider" placeholder="e.g. Apple Genius Bar, Dell ProSupport" required />
                        <InputError :message="form.errors.provider" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="cost">Estimated Cost ($)</Label>
                        <Input id="cost" v-model="form.cost" type="number" step="0.01" min="0" placeholder="0.00" />
                        <InputError :message="form.errors.cost" />
                    </div>
                </div>

                <!-- Date started & status -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="started_at">Date Servicing Started</Label>
                        <Input id="started_at" v-model="form.started_at" type="date" />
                        <InputError :message="form.errors.started_at" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="status">Initial Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-9 w-full rounded-md border border-neutral-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                        >
                            <option value="in_progress">In Progress</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                </div>

                <!-- Diagnostic Notes -->
                <div class="space-y-1.5">
                    <Label for="notes">Diagnostic & Hardware Notes</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        placeholder="Detailed symptom description, error codes, tracking numbers..."
                        class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-neutral-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    ></textarea>
                    <InputError :message="form.errors.notes" />
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Submit Repair Ticket' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
