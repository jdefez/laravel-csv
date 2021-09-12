<?php

namespace Jdefez\LaravelCsv;

use Jdefez\LaravelCsv\Csv\CsvReadable;
use Jdefez\LaravelCsv\Csv\CsvWritable;
use Jdefez\LaravelCsv\Csv\Reader;
use Jdefez\LaravelCsv\Csv\Writer;
use SplFileObject;

class Csv implements Csvable
{
    public static function reader(SplFileObject $file): CsvReadable
    {
        return new Reader($file);
    }

    public static function writer(SplFileObject $file): CsvWritable
    {
        return new Writer($file);
    }

    public static function fakeReader(array $lines, ?int $maxMemory = null): CsvReadable
    {
        return Reader::fake($lines, $maxMemory);
    }

    public static function fakeWriter(?int $maxMemory = null): CsvWritable
    {
        return Writer::fake($maxMemory);
    }
}
