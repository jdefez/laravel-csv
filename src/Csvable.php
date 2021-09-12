<?php

namespace Jdefez\LaravelCsv;

use Jdefez\LaravelCsv\Csv\CsvReadable;
use Jdefez\LaravelCsv\Csv\CsvWritable;
use SplFileObject;

interface Csvable
{
    public static function reader(SplFileObject $file): CsvReadable;

    public static function writer(SplFileObject $file): CsvWritable;

    public static function fakeReader(array $lines, ?int $maxMemory = null): CsvReadable;

    public static function fakeWriter(?int $maxMemory = null): CsvWritable;
}
