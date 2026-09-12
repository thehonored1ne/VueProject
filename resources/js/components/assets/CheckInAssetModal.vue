<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Asset } from '@/types/asset';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    asset: Asset | null;
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    target_status: 'available',
    condition_on_return: 'Good working condition, wiped and clean.',
    notes: '',
});

function handleSubmit() {
    if (!props.asset) return;

    form.post(route('assets.check-in', props.asset.id), {
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
                <DialogTitle>Check In Asset</DialogTitle>
                <DialogDescription>
                    Process return for <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ asset?.asset_tag }}</span> currently held
                    by
                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ asset?.current_assignment?.user?.name ?? 'Employee' }}</span
                    >.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Target Status -->
                <div class="space-y-1.5">
                    <Label for="target_status">Destination Status</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            :class="[
                                'flex flex-col items-start rounded-lg border p-3 text-left transition-all',
                                form.target_status === 'available'
                                    ? 'border-emerald-600 bg-emerald-50/50 text-emerald-900 ring-1 ring-emerald-600 dark:border-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-200'
                                    : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-800 dark:hover:border-neutral-700',
                            ]"
                            @click="form.target_status = 'available'"
                        >
                            <span class="text-xs font-semibold">Available</span>
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Ready for redeployment</span>
                        </button>

                        <button
                            type="button"
                            :class="[
                                'flex flex-col items-start rounded-lg border p-3 text-left transition-all',
                                form.target_status === 'maintenance'
                                    ? 'border-amber-600 bg-amber-50/50 text-amber-900 ring-1 ring-amber-600 dark:border-amber-500 dark:bg-amber-950/30 dark:text-amber-200'
                                    : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-800 dark:hover:border-neutral-700',
                            ]"
                            @click="form.target_status = 'maintenance'"
                        >
                            <span class="text-xs font-semibold">Maintenance</span>
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Needs repair or cleaning</span>
                        </button>
                    </div>
                    <p v-if="form.errors.target_status" class="text-xs text-rose-500">{{ form.errors.target_status }}</p>
                </div>

                <!-- Received Condition -->
                <div class="space-y-1.5">
                    <Label for="condition_on_return">Condition on Return</Label>
                    <Input id="condition_on_return" v-model="form.condition_on_return" placeholder="e.g. Good, normal wear, minor chassis scuff" />
                    <p v-if="form.errors.condition_on_return" class="text-xs text-rose-500">{{ form.errors.condition_on_return }}</p>
                </div>

                <!-- Return Notes -->
                <div class="space-y-1.5">
                    <Label for="return_notes">Return Notes</Label>
                    <textarea
                        id="return_notes"
                        v-model="form.notes"
                        rows="2"
                        placeholder="Optional inspection details or diagnostic logs..."
                        class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    />
                    <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing"> Confirm Return </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
