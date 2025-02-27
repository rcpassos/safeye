<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CheckHistoryType: string implements HasLabel
{
    use ExtraMethods;

    case SUCCESS = 'success';
    case ERROR = 'error';

    public function getLabel(): string
    {
        return $this->value;
    }
}
