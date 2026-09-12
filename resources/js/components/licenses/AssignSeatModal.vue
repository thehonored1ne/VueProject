<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import type { UserSummary } from '@/types/asset';
import type { SoftwareLicense } from '@/types/license';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    license: SoftwareLicense | null;
    users: UserSummary[];
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    user_id: '',
    notes: '',
});

function handleSubmit() {
    if (!props.license) return;

    form.post(route('licenses.assign', props.license.id), {
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
                <DialogTitle>Allocate License Seat</DialogTitle>
                <DialogDescription>
                    Assign a seat for <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ license?.name }}</span>
                    to a team member.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Employee Select -->
                <div class="space-y-1.5">
                    <Label for="seat_user_id">Team Member <span class="text-rose-500">*</span></Label>
                    <select
                        id="seat_user_id"
                        v-model="form.user_id"
                        required
                        class="flex h-9 w-full rounded-md border border-neutral-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    >
                        <option value="" disabled>Select employee...</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
                    </select>
                    <p v-if="form.errors.user_id" class="text-xs text-rose-500">{{ form.errors.user_id }}</p>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <Label for="seat_notes">Allocation Notes</Label>
                    <textarea
                        id="seat_notes"
                        v-model="form.notes"
                        rows="2"
                        placeholder="e.g. Project allocation, primary IDE assignment..."
                        class="flex w-full rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:focus-visible:ring-neutral-300"
                    />
                    <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing"> Allocate Seat </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
