<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;

interface Readable
{
    public function read(): Generator;
}
