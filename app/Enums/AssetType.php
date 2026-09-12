<?php

declare(strict_types=1);

namespace App\Enums;

enum AssetType: string
{
    case Laptop = 'laptop';
    case Desktop = 'desktop';
    case Monitor = 'monitor';
    case Mobile = 'mobile';
    case Server = 'server';
    case Peripheral = 'peripheral';

    public function label(): string
    {
        return match ($this) {
            self::Laptop => 'Laptop',
            self::Desktop => 'Desktop',
            self::Monitor => 'Monitor',
            self::Mobile => 'Mobile Device',
            self::Server => 'Server',
            self::Peripheral => 'Peripheral',
        };
    }
}
