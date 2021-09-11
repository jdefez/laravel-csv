<?php

namespace Jdefez\LaravelCsv\Csv;

use Illuminate\Support\Collection;
use SplFileObject;

interface CsvWritable
{
    public static function setFile(SplFileObject $file): CsvWritable;

    public function setData(Collection $data): CsvWritable;

    public function write(?callable $mapping = null): void;

    public function setDelimiter(string $delimiter): CsvWritable;

    public function setEnclosure(string $enclosure): CsvWritable;

    public function setEscape(string $escape): CsvWritable;

    public function setColumns(array $columns): CsvWritable;
}

