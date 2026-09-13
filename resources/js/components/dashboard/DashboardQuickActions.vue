<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Download, KeyRound, Laptop, PlusCircle, Wrench, Zap } from 'lucide-vue-next';
</script>

<template>
    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center gap-2 border-b border-neutral-100 pb-3 dark:border-neutral-800">
            <Zap class="h-4 w-4 text-amber-500 dark:text-amber-400" />
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Quick Launch Workflows</h2>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-1">
            <Link
                :href="route('assets.create')"
                class="flex items-center gap-2.5 rounded-lg border border-neutral-200/80 p-2.5 text-xs font-medium text-neutral-700 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-md bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                >
                    <PlusCircle class="h-3.5 w-3.5" />
                </div>
                <span>Register Hardware Asset</span>
            </Link>

            <Link
                :href="route('assets.index')"
                class="flex items-center gap-2.5 rounded-lg border border-neutral-200/80 p-2.5 text-xs font-medium text-neutral-700 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                <div class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <Laptop class="h-3.5 w-3.5" />
                </div>
                <span>Assign & Check Out Fleet</span>
            </Link>

            <Link
                :href="route('licenses.create')"
                class="flex items-center gap-2.5 rounded-lg border border-neutral-200/80 p-2.5 text-xs font-medium text-neutral-700 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400"
                >
                    <KeyRound class="h-3.5 w-3.5" />
                </div>
                <span>Add Software License</span>
            </Link>

            <Link
                :href="route('maintenance.index')"
                class="flex items-center gap-2.5 rounded-lg border border-neutral-200/80 p-2.5 text-xs font-medium text-neutral-700 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                <div class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <Wrench class="h-3.5 w-3.5" />
                </div>
                <span>Schedule Repair Ticket</span>
            </Link>

            <a
                :href="route('assets.export')"
                class="flex items-center gap-2.5 rounded-lg border border-neutral-200/80 p-2.5 text-xs font-medium text-neutral-700 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <Download class="h-3.5 w-3.5" />
                </div>
                <span>Stream Export CSV</span>
            </a>
        </div>
    </div>
</template>
