<?php

declare(strict_types=1);

namespace App\DTOs\Maintenance;

use App\Enums\MaintenanceStatus;

final readonly class MaintenanceData
{
    public function __construct(
        public int $assetId,
        public string $title,
        public string $provider,
        public ?float $cost = null,
        public MaintenanceStatus $status = MaintenanceStatus::InProgress,
        public ?string $scheduledAt = null,
        public ?string $startedAt = null,
        public ?string $notes = null,
    ) {}
}
