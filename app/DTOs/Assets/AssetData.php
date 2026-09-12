<?php

declare(strict_types=1);

namespace App\DTOs\Assets;

use App\Enums\AssetStatus;
use App\Enums\AssetType;

readonly class AssetData
{
    public function __construct(
        public string $name,
        public AssetType $type,
        public ?string $assetTag = null,
        public ?AssetStatus $status = null,
        public ?string $serialNumber = null,
        public ?string $modelNumber = null,
        public ?float $cost = null,
        public ?string $purchasedAt = null,
        public ?string $warrantyExpiresAt = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            type: $data['type'] instanceof AssetType ? $data['type'] : AssetType::from((string) $data['type']),
            assetTag: isset($data['asset_tag']) && is_string($data['asset_tag']) ? $data['asset_tag'] : null,
            status: isset($data['status']) && $data['status'] ? ($data['status'] instanceof AssetStatus ? $data['status'] : AssetStatus::from((string) $data['status'])) : null,
            serialNumber: isset($data['serial_number']) && is_string($data['serial_number']) ? $data['serial_number'] : null,
            modelNumber: isset($data['model_number']) && is_string($data['model_number']) ? $data['model_number'] : null,
            cost: isset($data['cost']) && is_numeric($data['cost']) ? (float) $data['cost'] : null,
            purchasedAt: isset($data['purchased_at']) && is_string($data['purchased_at']) ? $data['purchased_at'] : null,
            warrantyExpiresAt: isset($data['warranty_expires_at']) && is_string($data['warranty_expires_at']) ? $data['warranty_expires_at'] : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'type' => $this->type->value,
            'asset_tag' => $this->assetTag,
            'status' => $this->status?->value,
            'serial_number' => $this->serialNumber,
            'model_number' => $this->modelNumber,
            'cost' => $this->cost,
            'purchased_at' => $this->purchasedAt,
            'warranty_expires_at' => $this->warrantyExpiresAt,
            'notes' => $this->notes,
        ], fn ($val) => $val !== null);
    }
}
