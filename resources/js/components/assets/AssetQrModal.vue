<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { Asset } from '@/types/asset';
import { Check, Copy, Printer } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { ref, watch } from 'vue';

const props = defineProps<{
    asset: Asset | null;
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const qrDataUrl = ref<string>('');
const isCopied = ref(false);

watch(
    () => [props.open, props.asset],
    async () => {
        if (!props.open || !props.asset) {
            qrDataUrl.value = '';
            return;
        }

        const assetUrl = `${window.location.origin}/assets/${props.asset.id}`;
        try {
            qrDataUrl.value = await QRCode.toDataURL(assetUrl, {
                width: 256,
                margin: 1,
                color: {
                    dark: '#0f172a',
                    light: '#ffffff',
                },
                errorCorrectionLevel: 'M',
            });
        } catch (err) {
            console.error('Failed to generate QR code:', err);
        }
    },
    { immediate: true },
);

function handlePrint() {
    window.print();
}

async function handleCopyUrl() {
    if (!props.asset) return;
    const url = `${window.location.origin}/assets/${props.asset.id}`;
    await navigator.clipboard.writeText(url);
    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Asset Identification Label</DialogTitle>
                <DialogDescription>
                    Physical hardware QR label for <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ asset?.asset_tag }}</span
                    >.
                </DialogDescription>
            </DialogHeader>

            <!-- Printable Physical Tag Surface -->
            <div class="flex justify-center py-2">
                <div
                    id="printable-qr-badge"
                    class="w-full max-w-[340px] rounded-xl border border-neutral-300 bg-white p-5 text-neutral-900 shadow-sm transition-all dark:border-neutral-700"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                        <div class="flex items-center space-x-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-neutral-900 text-xs font-bold text-white">AF</div>
                            <span class="text-xs font-bold uppercase tracking-wider text-neutral-700">AssetFlow Registry</span>
                        </div>
                        <span class="rounded bg-neutral-100 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-neutral-600">
                            {{ asset?.type }}
                        </span>
                    </div>

                    <!-- QR Code & Tag Body -->
                    <div class="my-4 flex flex-col items-center">
                        <div class="rounded-lg border border-neutral-200 bg-white p-2.5 shadow-inner">
                            <img v-if="qrDataUrl" :src="qrDataUrl" :alt="`QR Code for ${asset?.asset_tag}`" class="h-44 w-44 object-contain" />
                            <div v-else class="flex h-44 w-44 items-center justify-center bg-neutral-100 text-xs text-neutral-400">
                                Generating QR...
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <span class="block font-mono text-xl font-bold tracking-wider text-neutral-950">
                                {{ asset?.asset_tag }}
                            </span>
                            <span class="mt-0.5 line-clamp-1 block max-w-[280px] text-xs font-medium text-neutral-700">
                                {{ asset?.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Meta Specs Barcode Grid -->
                    <div class="space-y-1 border-t border-neutral-200 pt-2.5 text-[11px] text-neutral-600">
                        <div v-if="asset?.serial_number" class="flex justify-between">
                            <span class="font-medium text-neutral-500">Serial:</span>
                            <span class="font-mono text-neutral-900">{{ asset.serial_number }}</span>
                        </div>
                        <div v-if="asset?.model_number" class="flex justify-between">
                            <span class="font-medium text-neutral-500">Model:</span>
                            <span class="font-mono text-neutral-900">{{ asset.model_number }}</span>
                        </div>
                    </div>

                    <!-- Scan Instruction -->
                    <div class="mt-3 text-center text-[10px] text-neutral-400">Scan to verify hardware custody & specifications</div>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-between sm:space-x-2">
                <Button type="button" variant="outline" size="sm" @click="handleCopyUrl" class="gap-1.5">
                    <Check v-if="isCopied" class="h-3.5 w-3.5 text-emerald-600" />
                    <Copy v-else class="h-3.5 w-3.5" />
                    {{ isCopied ? 'Link Copied' : 'Copy URL' }}
                </Button>

                <div class="flex items-center space-x-2">
                    <Button type="button" variant="outline" size="sm" @click="emit('update:open', false)"> Close </Button>
                    <Button type="button" size="sm" @click="handlePrint" class="gap-1.5">
                        <Printer class="h-3.5 w-3.5" />
                        Print Sticker
                    </Button>
                </div>
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

    /* Show only the QR badge and its contents */
    #printable-qr-badge,
    #printable-qr-badge * {
        visibility: visible !important;
    }

    #printable-qr-badge {
        position: fixed !important;
        left: 50% !important;
        top: 50% !important;
        transform: translate(-50%, -50%) !important;
        border: 2px solid #0f172a !important;
        padding: 16px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        width: 320px !important;
        max-width: 320px !important;
    }
}
</style>
