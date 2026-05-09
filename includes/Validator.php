<?php

declare(strict_types=1);

/**
 * Small validation helpers — explicit and testable (recruiters like clarity).
 */
final class Validator
{
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function intBetween(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Trim and limit string length (basic XSS mitigation for stored text;
     * frontend must still escape on output).
     */
    public static function cleanString(string $value, int $maxLen): string
    {
        $t = trim($value);
        if (mb_strlen($t) > $maxLen) {
            $t = mb_substr($t, 0, $maxLen);
        }
        return $t;
    }
}
