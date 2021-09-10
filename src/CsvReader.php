<?php

namespace Jdefez\LaravelCsv;

use SplFileObject;
use Generator;

class CsvReader
{
    public SplFileObject $file;

    private string $path;

    private string $separator = ';';

    private string $enclosure = '"';

    private string $escape = '\\';

    public function __construct(SplFileObject $file)
    {
        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );

        $this->file = $file;
    }

    public static function setFile(SplFileObject $file)
    {
        return new self($file);
    }

    public function read(): Generator
    {
        while (!$this->file->eof()) {
            $line = $this->file->fgetcsv(
                $this->separator,
                $this->enclosure,
                $this->escape
            );

            if ($line) {
                yield $line;
            }
        }
    }
}
