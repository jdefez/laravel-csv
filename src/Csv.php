<?php

namespace Jdefez\LaravelCsv;

use Jdefez\LaravelCsv\Csv\Readable;
use Jdefez\LaravelCsv\Csv\Writable;
use Jdefez\LaravelCsv\Csv\Reader;
use Jdefez\LaravelCsv\Csv\Writer;
use SplFileObject;

class Csv implements Csvable
{
    public static function reader(SplFileObject $file): Readable
    {
        return new Reader($file);
    }

    public static function writer(SplFileObject $file): Writable
    {
        return new Writer($file);
    }

    public static function fakeReader(array $lines, ?int $maxMemory = null): Readable
    {
        return Reader::fake($lines, $maxMemory);
    }

    public static function fakeWriter(?int $maxMemory = null): Writable
    {
        return Writer::fake($maxMemory);
    }
}
