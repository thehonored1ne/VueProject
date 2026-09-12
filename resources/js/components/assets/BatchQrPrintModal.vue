<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { Asset } from '@/types/asset';
import { Printer } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { ref, watch } from 'vue';

const props = defineProps<{
    assets: Asset[];
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const qrMap = ref<Record<number, string>>({});
const isGenerating = ref(false);

watch(
    () => [props.open, props.assets],
    async () => {
        if (!props.open || !props.assets.length) {
            qrMap.value = {};
            return;
        }

        isGenerating.value = true;
        const newMap: Record<number, string> = {};

        try {
            for (const asset of props.assets) {
                const assetUrl = `${window.location.origin}/assets/${asset.id}`;
                newMap[asset.id] = await QRCode.toDataURL(assetUrl, {
                    width: 160,
                    margin: 1,
                    color: {
                        dark: '#0f172a',
                        light: '#ffffff',
                    },
                });
            }
            qrMap.value = newMap;
        } catch (err) {
            console.error('Failed to generate batch QR codes:', err);
        } finally {
            isGenerating.value = false;
        }
    },
    { immediate: true },
);

function handlePrint() {
    window.print();
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] max-w-4xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Printable Asset Label Sheet</DialogTitle>
                <DialogDescription>
                    Printable batch sticker sheet for {{ assets.length }} inventory {{ assets.length === 1 ? 'asset' : 'assets' }}.
                </DialogDescription>
            </DialogHeader>

            <div v-if="isGenerating" class="flex flex-col items-center justify-center py-12 text-sm text-neutral-500">
                <span class="animate-pulse">Generating label badges...</span>
            </div>

            <div v-else class="py-2">
                <!-- Batch Print Container -->
                <div id="printable-batch-sheet" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="asset in assets"
                        :key="asset.id"
                        class="batch-label-item rounded-lg border border-neutral-300 bg-white p-3.5 text-neutral-900 shadow-sm dark:border-neutral-700"
                    >
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-2">
                            <div class="flex items-center space-x-1.5">
                                <span class="rounded bg-neutral-900 px-1 py-0.5 text-[9px] font-bold text-white">AF</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-700">AssetFlow</span>
                            </div>
                            <span class="font-mono text-[9px] font-medium uppercase text-neutral-500">{{ asset.type }}</span>
                        </div>

                        <div class="my-2.5 flex items-center space-x-3">
                            <div class="flex-shrink-0 rounded border border-neutral-200 bg-white p-1">
                                <img v-if="qrMap[asset.id]" :src="qrMap[asset.id]" :alt="`QR ${asset.asset_tag}`" class="h-20 w-20 object-contain" />
                                <div v-else class="flex h-20 w-20 items-center justify-center bg-neutral-100 text-[10px] text-neutral-400">QR</div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <span class="block font-mono text-sm font-bold text-neutral-950">{{ asset.asset_tag }}</span>
                                <span class="mt-0.5 block truncate text-xs font-medium text-neutral-800">{{ asset.name }}</span>
                                <span v-if="asset.serial_number" class="mt-1 block truncate font-mono text-[10px] text-neutral-500">
                                    SN: {{ asset.serial_number }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-neutral-100 pt-1.5 text-center text-[9px] text-neutral-400">Scan to inspect custody & specs</div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex justify-between sm:justify-between">
                <Button type="button" variant="outline" size="sm" @click="emit('update:open', false)"> Cancel </Button>
                <Button type="button" size="sm" @click="handlePrint" :disabled="isGenerating" class="gap-1.5">
                    <Printer class="h-3.5 w-3.5" />
                    Print Sticker Sheet
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style>
@media print {
    /* Hide everything on page */
    body * {
        visibility: hidden !important;
    }

    /* Show only the batch sheet and its contents */
    #printable-batch-sheet,
    #printable-batch-sheet * {
        visibility: visible !important;
    }

    #printable-batch-sheet {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
        padding: 20px !important;
        background: #ffffff !important;
        color: #0f172a !important;
    }

    .batch-label-item {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        background: #ffffff !important;
    }
}
</style>
