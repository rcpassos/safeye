<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CheckType: string implements HasLabel
{
    use ExtraMethods;

    case HTTP = 'http';

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
