<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-blue-500/10 p-1 dark:bg-blue-400/10">
        <AppLogoIcon class="size-6" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none text-neutral-900 dark:text-neutral-100">AssetFlow</span>
    </div>
</template>
