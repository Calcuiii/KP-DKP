<?php

namespace Tests\Unit;

use App\Support\SafeCsv;
use PHPUnit\Framework\TestCase;

class SafeCsvTest extends TestCase
{
    public function test_untrusted_formula_prefixes_are_literal_and_quotes_are_escaped(): void
    {
        foreach (['=1+1', '+SUM(1,2)', '-1+2', '@SUM(1,2)', "\t=1", '  =1', "\rformula"] as $value) {
            self::assertStringStartsWith('"\'', SafeCsv::cell($value));
        }
        self::assertSame('"Pertanyaan ""aman"""', SafeCsv::cell('Pertanyaan "aman"'));
        self::assertSame('"Halo"', SafeCsv::cell('Halo'));
    }
}
