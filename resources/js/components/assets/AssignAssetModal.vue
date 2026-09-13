<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Asset, UserSummary } from '@/types/asset';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    asset: Asset | null;
    users: UserSummary[];
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    user_id: '',
    expected_return_at: '',
    condition_on_assignment: 'Pristine condition, verified working.',
    notes: '',
});

function handleSubmit() {
    if (!props.asset) return;

    form.post(route('assets.assign', props.asset.id), {
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
                <DialogTitle>Check Out Asset</DialogTitle>
                <DialogDescription>
                    Assign <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ asset?.asset_tag }}</span> ({{ asset?.name }}) to a
                    team member.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Assignee Select -->
                <div class="space-y-1.5">
                    <Label for="user_id">Assign To Employee <span class="text-rose-500">*</span></Label>
                    <select
                        id="user_id"
                        v-model="form.user_id"
                        required
                        class="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm text-neutral-900 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus-visible:ring-neutral-300"
                    >
                        <option value="" disabled>Select employee...</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
                    </select>
                    <p v-if="form.errors.user_id" class="text-xs text-rose-500">{{ form.errors.user_id }}</p>
                </div>

                <!-- Expected Return Date -->
                <div class="space-y-1.5">
                    <Label for="expected_return_at">Expected Return Date</Label>
                    <Input id="expected_return_at" type="date" v-model="form.expected_return_at" />
                    <p v-if="form.errors.expected_return_at" class="text-xs text-rose-500">{{ form.errors.expected_return_at }}</p>
                </div>

                <!-- Outgoing Condition -->
                <div class="space-y-1.5">
                    <Label for="condition_on_assignment">Condition on Checkout</Label>
                    <Input
                        id="condition_on_assignment"
                        v-model="form.condition_on_assignment"
                        placeholder="e.g. Pristine, new charger, FileVault enabled"
                    />
                    <p v-if="form.errors.condition_on_assignment" class="text-xs text-rose-500">{{ form.errors.condition_on_assignment }}</p>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <Label for="notes">Assignment Notes</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        placeholder="Optional operational or project notes..."
                        class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    />
                    <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing"> Confirm Check Out </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
