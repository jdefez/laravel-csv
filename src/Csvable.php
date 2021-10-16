<?php

namespace Jdefez\LaravelCsv;

use Jdefez\LaravelCsv\Csv\Readable;
use Jdefez\LaravelCsv\Csv\Writable;
use SplFileObject;

interface Csvable
{
    public static function reader(SplFileObject $file): Readable;

    public static function writer(SplFileObject $file): Writable;

    public static function fakeReader(array $lines, ?int $maxMemory = null): Readable;

    public static function fakeWriter(?int $maxMemory = null): Writable;
}
