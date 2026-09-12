<?php

declare(strict_types=1);

namespace App\DTOs\Assets;

readonly class AssignAssetData
{
    public function __construct(
        public int $userId,
        public ?string $expectedReturnAt = null,
        public ?string $conditionOnAssignment = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            expectedReturnAt: isset($data['expected_return_at']) && is_string($data['expected_return_at']) ? $data['expected_return_at'] : null,
            conditionOnAssignment: isset($data['condition_on_assignment']) && is_string($data['condition_on_assignment']) ? $data['condition_on_assignment'] : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }
}
