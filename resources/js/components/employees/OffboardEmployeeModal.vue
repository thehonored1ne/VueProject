<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import type { Employee } from '@/types/employee';
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, Key, Laptop, UserMinus } from 'lucide-vue-next';

const props = defineProps<{
    employee: Employee;
    open: boolean;
    activeHardwareCount: number;
    activeLicensesCount: number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    notes: '',
});

function handleSubmit() {
    form.post(route('employees.offboard', props.employee.id), {
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
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400"
                    >
                        <UserMinus class="h-5 w-5" />
                    </div>
                    <div>
                        <DialogTitle>Offboard Employee Custody</DialogTitle>
                        <DialogDescription class="mt-1">
                            Atomic offboarding workflow for
                            <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ employee.name }}</span
                            >.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Warning Notice -->
                <div
                    class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50/60 p-3 text-xs text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
                    <div>
                        <p class="font-medium">Single-Transaction Custody Release</p>
                        <p class="mt-0.5 opacity-90">
                            Submitting this will mark all active hardware as returned (status restored to Available) and revoke all software license
                            seats assigned to this employee.
                        </p>
                    </div>
                </div>

                <!-- Custody Impact Summary -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-neutral-800 dark:bg-neutral-900/50">
                        <div class="flex items-center gap-2 text-xs font-medium text-neutral-500 dark:text-neutral-400">
                            <Laptop class="h-3.5 w-3.5" />
                            Hardware Return
                        </div>
                        <p class="mt-1 text-lg font-bold tabular-nums text-neutral-900 dark:text-neutral-100">
                            {{ activeHardwareCount }}
                            <span class="text-xs font-normal text-neutral-500 dark:text-neutral-400">
                                {{ activeHardwareCount === 1 ? 'device' : 'devices' }}
                            </span>
                        </p>
                    </div>

                    <div class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-neutral-800 dark:bg-neutral-900/50">
                        <div class="flex items-center gap-2 text-xs font-medium text-neutral-500 dark:text-neutral-400">
                            <Key class="h-3.5 w-3.5" />
                            Seat Revocation
                        </div>
                        <p class="mt-1 text-lg font-bold tabular-nums text-neutral-900 dark:text-neutral-100">
                            {{ activeLicensesCount }}
                            <span class="text-xs font-normal text-neutral-500 dark:text-neutral-400">
                                {{ activeLicensesCount === 1 ? 'seat' : 'seats' }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Return / Offboarding Notes -->
                <div class="space-y-1.5">
                    <Label for="offboard_notes">Offboarding Audit Notes</Label>
                    <textarea
                        id="offboard_notes"
                        v-model="form.notes"
                        rows="3"
                        placeholder="e.g. Employee offboarding complete. Equipment collected and verified in good condition by IT."
                        class="flex w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 shadow-sm placeholder:text-neutral-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500 dark:focus-visible:ring-neutral-300"
                    ></textarea>
                    <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button type="button" variant="outline" size="sm" @click="emit('update:open', false)"> Cancel </Button>
                    <Button type="submit" variant="destructive" size="sm" :disabled="form.processing" class="gap-1.5">
                        <UserMinus class="h-3.5 w-3.5" />
                        {{ form.processing ? 'Offboarding...' : 'Confirm Offboarding' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
