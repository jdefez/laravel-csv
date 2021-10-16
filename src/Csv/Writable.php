<?php

namespace Jdefez\LaravelCsv\Csv;

use Illuminate\Support\Collection;
use SplFileObject;

interface Writable
{
    public static function setFile(SplFileObject $file): self;

    public static function fake(?int $maxMemory = null): self;

    public function setData(Collection $data): self;

    public function write(?callable $mapping = null): void;

    public function put(array $row): void;

    public function setDelimiter(string $delimiter): self;

    public function setEnclosure(string $enclosure): self;

    public function setEscape(string $escape): self;

    public function setColumns(array $columns): self;
}
