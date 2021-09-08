<?php

namespace Jdefez\LaravelCsv;

use SplFileObject;

class CsvReader
{
    public SplFileObject $file;

    private string $path;


    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function read()
    {
        $this->file = new SplFileObject($this->path);
    }
}
