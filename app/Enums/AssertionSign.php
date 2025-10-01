<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssertionSign: string implements HasLabel
{
    use ExtraMethods;

    case EQUAL = 'eq';
    case NOT_EQUAL = 'neq';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';

    public function getLabel(): string
    {
        return match ($this) {
            self::EQUAL => __('assertion_signs.equal'),
            self::NOT_EQUAL => __('assertion_signs.not_equal'),
            self::GREATER_THAN => __('assertion_signs.greater_than'),
            self::GREATER_THAN_OR_EQUAL => __('assertion_signs.greater_than_or_equal'),
            self::LESS_THAN => __('assertion_signs.less_than'),
            self::LESS_THAN_OR_EQUAL => __('assertion_signs.less_than_or_equal'),
        };
    }
}
