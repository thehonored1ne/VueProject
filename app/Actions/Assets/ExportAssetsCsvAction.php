<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\Models\Asset;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportAssetsCsvAction
{
    /**
     * Generate a streamed CSV download of assets matching filters.
     */
    public function execute(?string $search = null, ?string $status = null, ?string $type = null): StreamedResponse
    {
        $fileName = 'assetflow-inventory-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($search, $status, $type): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Asset Tag',
                'Name',
                'Type',
                'Model Number',
                'Serial Number',
                'Status',
                'Cost ($)',
                'Current Assignee',
                'Assignee Email',
                'Purchase Date',
                'Warranty Expiration',
                'Notes',
            ]);

            Asset::query()
                ->search($search)
                ->filterStatus($status)
                ->filterType($type)
                ->with(['currentAssignment.user'])
                ->orderBy('id')
                ->chunkById(200, function ($assets) use ($handle): void {
                    foreach ($assets as $asset) {
                        $assignee = $asset->currentAssignment?->user;

                        fputcsv($handle, [
                            $asset->asset_tag,
                            $asset->name,
                            $asset->type?->value ?? (string) $asset->type,
                            $asset->model_number ?? '',
                            $asset->serial_number ?? '',
                            $asset->status?->value ?? (string) $asset->status,
                            $asset->cost ? number_format((float) $asset->cost, 2, '.', '') : '0.00',
                            $assignee ? $assignee->name : 'Unassigned',
                            $assignee ? $assignee->email : '',
                            $asset->purchased_at?->format('Y-m-d') ?? '',
                            $asset->warranty_expires_at?->format('Y-m-d') ?? '',
                            $asset->notes ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, $headers);
    }
}
