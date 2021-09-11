<?php

namespace Jdefez\LaravelCsv\Csv;

interface Writable
{
    public function write(?callable $mapping = null): void;
}
