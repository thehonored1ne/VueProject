<?php

declare(strict_types=1);

namespace App\DTOs\Licenses;

readonly class AssignSeatData
{
    public function __construct(
        public int $userId,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }
}
