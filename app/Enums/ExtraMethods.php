<?php

declare(strict_types=1);

namespace App\Enums;

trait ExtraMethods
{
    /**
     * Returns only the values of each option from the enum.
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Returns only the values of each option from the enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Converts the Enum to a set of options to use in a select input.
     */
    public static function toSelect($prefix): array
    {
        $names = self::names();
        $values = self::values();

        $labels = array_map(fn (string $name) => __($prefix.'.'.mb_strtolower($name)), $names);

        return array_combine($values, $labels);
    }

    public static function getTranslationFromValue($value, string $prefix): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return __($prefix.'.'.mb_strtolower((string) $case->name));
            }
        }

        return null;
    }
}
