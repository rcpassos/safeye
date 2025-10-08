<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CheckType: string implements HasLabel
{
    use ExtraMethods;

    case HTTP = 'http';
    case PING = 'ping';

    public function getLabel(): string
    {
        return match ($this) {
            self::HTTP => __('check_types.http'),
            self::PING => __('check_types.ping'),
        };
    }
}
