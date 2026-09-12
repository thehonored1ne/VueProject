<?php

declare(strict_types=1);

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Available => 'emerald',
            self::Assigned => 'blue',
            self::Maintenance => 'amber',
            self::Retired => 'zinc',
        };
    }
}
