<?php

namespace Jdefez\LaravelCsv\Csv;

use Illuminate\Support\Collection;
use SplFileObject;

class Writer implements Writable
{
    public SplFileObject $file;

    private array $columns = [];

    private Collection $data;

    private string $delimiter = ';';

    private string $enclosure = '"';

    private ?string $escape = '\\';

    final public function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    public static function setFile(SplFileObject $file): self
    {
        return new static($file);
    }

    public static function fake(?int $maxMemory = null): self
    {
        return new static(File::fake(null, $maxMemory));
    }

    public function setData(Collection $data): self
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

            $this->putRow($row);
        });
    }

    public function put(array $row): void
    {
        $this->putRow($row);
    }

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    public function setEscape(string $escape): self
    {
        $this->escape = $escape;

        return $this;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    protected function putRow(array $values): void
    {
        $this->file->fputcsv(
            array_values($values),
            $this->delimiter,
            $this->enclosure,
            $this->escape
        );
    }
}
