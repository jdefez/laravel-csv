<?php

namespace Jdefez\LaravelCsv\Csv;

use SplTempFileObject;

class File extends SplTempFileObject
{
    public static function fake(?array $rows = null, $maxMemory = null): SplTempFileObject
    {
        $file = new SplTempFileObject($maxMemory);

        if (! empty($rows)) {
            $file->fwrite(implode(PHP_EOL, $rows));
            $file->rewind();
        }

        return $file;
    }
}
