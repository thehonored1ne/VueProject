<?php

declare(strict_types=1);

namespace App\DTOs\Assets;

use App\Enums\AssetStatus;

readonly class CheckInAssetData
{
    public function __construct(
        public AssetStatus $targetStatus,
        public ?string $conditionOnReturn = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $statusInput = $data['target_status'] ?? AssetStatus::Available->value;
        $targetStatus = $statusInput instanceof AssetStatus ? $statusInput : AssetStatus::from((string) $statusInput);

        return new self(
            targetStatus: $targetStatus,
            conditionOnReturn: isset($data['condition_on_return']) && is_string($data['condition_on_return']) ? $data['condition_on_return'] : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }
}
