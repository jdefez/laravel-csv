<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use SplFileObject;

interface CsvReadable
{
    public static function setFile(SplFileObject $file): CsvReadable;

    public static function fake(array $lines, ?int $maxMemory = null): CsvReadable;

    public function setSearchEncodings(array $encodings): CsvReadable;

    public function keyByColumnName(): CsvReadable;

    public function setToEncoding(string $to_encoding): CsvReadable;

    public function withHeadings(): CsvReadable;

    public function setDelimiter(string $delimiter): CsvReadable;

    public function toObject(): CsvReadable;

    public function read(?callable $callback = null): Generator;
}
