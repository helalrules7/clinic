<?php

namespace App\Lib;

/**
 * Unicode digit equivalence — ASCII 0-9, Arabic-Indic ٠-٩, Extended ۰-۹.
 * Used for search, phone validation, and patient field storage.
 */
class DigitNormalizer
{
    /** @var array<string, string>|null */
    private static $digitMap = null;

    private static function digitMap(): array
    {
        if (self::$digitMap !== null) {
            return self::$digitMap;
        }
        $from = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $to   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        self::$digitMap = array_combine($from, $to);
        return self::$digitMap;
    }

    public static function toAsciiDigits(string $input): string
    {
        if ($input === '') {
            return '';
        }
        $map = self::digitMap();
        $len = mb_strlen($input, 'UTF-8');
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($input, $i, 1, 'UTF-8');
            $out .= $map[$ch] ?? $ch;
        }
        return $out;
    }

    /** Trim + convert all digit scripts to ASCII; leave letters/punctuation intact. */
    public static function normalizeSearchQuery(string $query): string
    {
        return self::toAsciiDigits(trim($query));
    }

    /** Keep only ASCII digits after script normalization. */
    public static function digitsOnly(string $input): string
    {
        $ascii = self::toAsciiDigits($input);
        return preg_replace('/\D/u', '', $ascii) ?? '';
    }

    /**
     * Egyptian mobile core — strips +20/0 then non-digits.
     * ٠١٠٠… and 0100… yield the same digit string.
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = self::toAsciiDigits(trim($phone));
        $phone = preg_replace('/^(\+20|0)/', '', $phone);
        return self::digitsOnly($phone);
    }

    public static function isPhoneNumberSearch(string $query): bool
    {
        $clean = self::normalizePhone($query);
        if ($clean === '' || strlen($clean) < 9 || strlen($clean) > 11) {
            return false;
        }
        return $clean[0] === '1';
    }

    /** True when the value is only digits (any Unicode script) after normalization. */
    public static function isNumericString(string $value): bool
    {
        $ascii = self::toAsciiDigits(trim($value));
        return $ascii !== '' && preg_match('/^\d+$/', $ascii) === 1;
    }

    /** Normalize phone / national_id fields before validation & storage. */
    public static function normalizePatientNumericFields(array $data): array
    {
        foreach (['phone', 'alt_phone', 'emergency_phone', 'national_id'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && $data[$field] !== null) {
                $data[$field] = self::toAsciiDigits(trim((string) $data[$field]));
            }
        }
        return $data;
    }

    /**
     * SQL expression: normalize a column's digits for LIKE comparison.
     * Handles legacy rows stored with Arabic-Indic digits.
     */
    public static function sqlDigitsExpr(string $sqlColumn): string
    {
        $expr = $sqlColumn;
        foreach (self::digitMap() as $from => $to) {
            $expr = 'REPLACE(' . $expr . ', ' . self::sqlQuote($from) . ', ' . self::sqlQuote($to) . ')';
        }
        return $expr;
    }

    /**
     * LIKE patterns for Egyptian phone formats (expects ASCII-normalized core).
     *
     * @return string[]
     */
    public static function generatePhoneSearchPatterns(string $cleanQuery): array
    {
        $patterns = [
            "%{$cleanQuery}%",
            "%+20{$cleanQuery}%",
            "%0{$cleanQuery}%",
            "%20{$cleanQuery}%",
        ];

        if ($cleanQuery !== '' && $cleanQuery[0] === '1' && strlen($cleanQuery) > 9) {
            $tail = substr($cleanQuery, 1);
            $patterns[] = "%{$tail}%";
            $patterns[] = "%+20{$tail}%";
            $patterns[] = "%0{$tail}%";
            $patterns[] = "%20{$tail}%";
        }

        return array_values(array_unique($patterns));
    }

    private static function sqlQuote(string $char): string
    {
        return "'" . str_replace("'", "''", $char) . "'";
    }
}
