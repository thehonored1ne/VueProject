<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\Enums\AssetStatus;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OffboardEmployeeAction
{
    /**
     * Atomically offboard an employee by returning all active hardware and revoking all software license seats.
     *
     * @return array{assets_returned: int, licenses_revoked: int}
     */
    public function execute(User $user, ?string $notes = null, ?User $offboardedBy = null): array
    {
        return DB::transaction(function () use ($user, $notes, $offboardedBy) {
            $activeAssignments = $user->activeAssetAssignments()->with('asset')->get();
            $assetsReturnedCount = 0;

            $auditNote = $notes
                ? '[Offboarded by '.($offboardedBy?->name ?? 'System').']: '.$notes
                : '[Offboarded by '.($offboardedBy?->name ?? 'System').']';

            /** @var AssetAssignment $assignment */
            foreach ($activeAssignments as $assignment) {
                $assignment->update([
                    'returned_at' => now(),
                    'condition_on_return' => 'Returned on employee offboarding',
                    'notes' => $assignment->notes ? $assignment->notes."\n".$auditNote : $auditNote,
                ]);

                $assignment->asset->update([
                    'status' => AssetStatus::Available,
                ]);

                $assetsReturnedCount++;
            }

            $licensesRevokedCount = $user->licenseAssignments()->delete();

            return [
                'assets_returned' => $assetsReturnedCount,
                'licenses_revoked' => $licensesRevokedCount,
            ];
        });
    }
}
