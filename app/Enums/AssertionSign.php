<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssertionSign: string implements HasLabel
{
    use ExtraMethods;

    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL_TO = '<=';
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL_TO = '>=';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
