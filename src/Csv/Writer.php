<?php

namespace Jdefez\LaravelCsv\Csv;

use Illuminate\Support\Collection;
use SplFileObject;

class Writer implements Writable, CsvWritable
{
    private SplFileObject $file;

    private array $columns = [];

    private Collection $data;

    private string $delimiter = ';';

    private string $enclosure = ' ';

    private ?string $escape = '\\';

    private function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    public static function setFile(SplFileObject $file): CsvWritable
    {
        return new self($file);
    }

    public function setData(Collection $data): CsvWritable
    {
        $this->data = $data;

        return $this;
    }

    public function write(?callable $mapping = null): void
    {
        if (!empty($this->columns)) {
            $this->putRow($this->columns);
        }

        $this->data->each(function ($row) use ($mapping) {
            if ($mapping) {
                $row = $mapping($row);
            }

            $this->putRow(array_values($row));
        });
    }

    public function setDelimiter(string $delimiter): CsvWritable
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function setEnclosure(string $enclosure): CsvWritable
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    public function setEscape(string $escape): CsvWritable
    {
        $this->escape = $escape;

        return $this;
    }

    public function setColumns(array $columns): CsvWritable
    {
        $this->columns = $columns;

        return $this;
    }

    protected function putRow(array $values): void
    {
        $this->file->fputcsv(
            $values,
            $this->delimiter,
            $this->enclosure,
            $this->escape
        );
    }
}
