<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use SplFileObject;

interface Readable
{
    public static function setFile(SplFileObject $file): self;

    public static function fake(array $lines, ?int $maxMemory = null): self;

    public function setSearchEncodings(array $encodings): self;

    public function keyByColumnName(): self;

    public function setToEncoding(string $to_encoding): self;

    public function withHeadings(): self;

    public function setDelimiter(string $delimiter): self;

    public function toObject(): self;

    public function read(?callable $callback = null): Generator;
}
