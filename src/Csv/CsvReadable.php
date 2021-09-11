<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use Illuminate\Support\Collection;
use SplFileObject;

interface CsvReadable
{
    public static function setFile(SplFileObject $file): CsvReadable;

    public function mapFieldsWithHeadings(): CsvReadable;

    public function setEncodings(array $encodings);

    public function withHeadings(): CsvReadable;

    public function setDelimiter(string $delimiter): CsvReadable;

    public function toCollection(?callable $callback = null): Collection;

    public function read(): Generator;
}
