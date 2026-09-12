<script setup lang="ts">
import type { AssetStatus } from '@/types/asset';
import { computed } from 'vue';

const props = defineProps<{
    status: AssetStatus;
}>();

const config = computed(() => {
    switch (props.status) {
        case 'available':
            return {
                label: 'Available',
                badgeClass:
                    'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
                dotClass: 'bg-emerald-500',
            };
        case 'assigned':
            return {
                label: 'Assigned',
                badgeClass: 'bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
                dotClass: 'bg-blue-500',
            };
        case 'maintenance':
            return {
                label: 'Maintenance',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
                dotClass: 'bg-amber-500',
            };
        case 'retired':
        default:
            return {
                label: 'Retired',
                badgeClass: 'bg-neutral-100 text-neutral-600 border-neutral-200 dark:bg-neutral-800/50 dark:text-neutral-400 dark:border-neutral-700',
                dotClass: 'bg-neutral-400',
            };
    }
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-medium tracking-tight transition-colors',
            config.badgeClass,
        ]"
    >
        <span :class="['h-1.5 w-1.5 rounded-full', config.dotClass]" />
        {{ config.label }}
    </span>
</template>
