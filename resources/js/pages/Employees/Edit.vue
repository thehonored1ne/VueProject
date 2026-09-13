<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Employee } from '@/types/employee';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps<{
    employee: Employee;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
    {
        title: props.employee.name,
        href: route('employees.show', props.employee.id),
    },
    {
        title: 'Edit',
        href: route('employees.edit', props.employee.id),
    },
];

const form = useForm({
    name: props.employee.name,
    email: props.employee.email,
    password: '',
});

function submit() {
    form.put(route('employees.update', props.employee.id));
}
</script>

<template>
    <Head :title="`Edit ${employee.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 md:text-2xl">Edit Team Member</h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 sm:text-sm">Update employee profile information.</p>
                </div>

                <Button as-child variant="outline" size="sm">
                    <Link :href="route('employees.show', employee.id)">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Back
                    </Link>
                </Button>
            </div>

            <!-- Form Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Information -->
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Profile Details</h2>

                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <Label for="edit_name">Full Name <span class="text-rose-500">*</span></Label>
                            <Input id="edit_name" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Work Email -->
                        <div class="space-y-1.5">
                            <Label for="edit_email">Work Email <span class="text-rose-500">*</span></Label>
                            <Input id="edit_email" type="email" v-model="form.email" required />
                            <p v-if="form.errors.email" class="text-xs text-rose-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- New Password -->
                        <div class="space-y-1.5">
                            <Label for="edit_password">New Password (Optional)</Label>
                            <Input id="edit_password" type="password" v-model="form.password" placeholder="Leave blank to retain current password" />
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                Leave blank if you do not wish to reset or change the password.
                            </p>
                            <p v-if="form.errors.password" class="text-xs text-rose-500">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <Button as-child type="button" variant="outline" size="sm">
                            <Link :href="route('employees.show', employee.id)">Cancel</Link>
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'Updating...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
