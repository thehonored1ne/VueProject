<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AssetMaintenance } from '@/types/maintenance';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
    open: boolean;
    maintenance: AssetMaintenance | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    cost: '',
    notes: '',
});

watch(
    () => [props.open, props.maintenance],
    () => {
        if (props.open && props.maintenance) {
            form.cost = props.maintenance.cost ? String(props.maintenance.cost) : '';
            form.notes = '';
        }
    },
);

function handleSubmit() {
    if (!props.maintenance) return;

    form.post(route('maintenance.complete', props.maintenance.id), {
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
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Complete Maintenance Job</DialogTitle>
                <DialogDescription>
                    Mark repair for <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ maintenance?.asset?.asset_tag }}</span> as
                    resolved. The hardware will automatically be restored to Available.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 text-xs dark:border-neutral-800 dark:bg-neutral-800/40">
                    <div class="font-semibold text-neutral-900 dark:text-neutral-100">{{ maintenance?.title }}</div>
                    <div class="mt-1 text-neutral-500">Service Vendor: {{ maintenance?.provider }}</div>
                </div>

                <!-- Final Cost -->
                <div class="space-y-1.5">
                    <Label for="complete_cost">Final Servicing Cost ($)</Label>
                    <Input id="complete_cost" v-model="form.cost" type="number" step="0.01" min="0" placeholder="0.00" />
                    <InputError :message="form.errors.cost" />
                </div>

                <!-- Resolution Notes -->
                <div class="space-y-1.5">
                    <Label for="complete_notes">Resolution Details & Verification</Label>
                    <textarea
                        id="complete_notes"
                        v-model="form.notes"
                        rows="3"
                        placeholder="Work performed, replaced components, diagnostic benchmark results..."
                        class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-neutral-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    ></textarea>
                    <InputError :message="form.errors.notes" />
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" class="bg-emerald-600 text-white hover:bg-emerald-700" :disabled="form.processing">
                        {{ form.processing ? 'Resolving...' : 'Confirm & Restore to Available' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
