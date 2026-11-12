<?php

namespace App\Support;

final class SafeCsv
{
    public static function cell(mixed $value): string
    {
        $text = (string) $value;
        if (preg_match('/^[\s\x00-\x1f]*[=+@-]|^[\x00-\x1f]/u', $text)) {
            $text = "'".$text;
        }

        return '"'.str_replace('"', '""', $text).'"';
    }
}
