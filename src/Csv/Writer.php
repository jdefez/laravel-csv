<?php

namespace Jdefez\LaravelCsv\Csv;

use Illuminate\Support\Collection;
use SplFileObject;

class Writer
{
    public SplFileObject $file;

    private array $columns = [];

    private Collection $data;

    private string $separator = ';';

    private string $enclosure = '"';

    private ?string $escape = '\\';

    // private ?string $eol = PHP_EOL; (php@8.1 only)

    final public function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * Sets the file to be used to store datas.
     */
    public static function setFile(SplFileObject $file): static
    {
        return new static($file);
    }

    /**
     * Returns a new Writer instance based on a SplTempFileObject.
     */
    public static function fake(?int $maxMemory = null): static
    {
        return new static(File::fake(null, $maxMemory));
    }

    /**
     * Sets the collection to be stored
     */
    public function setData(Collection $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Writes the entire collection to the file.
     */
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

    /**
     * Writes a single row to the file
     */
    public function put(array|callable $row): void
    {
        if (is_callable($row)) {
            $row = $row();
        }

        $this->putRow($row);
    }

    public function setSeparator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Sets the enclosure to be used to parse the file.
     */
    public function setEnclosure(string $enclosure): static
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Sets the escape to be used to parse the file.
     */
    public function setEscape(string $escape): static
    {
        $this->escape = $escape;

        return $this;
    }

    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * Sets the row to be used as columns headings.
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets the eol to be used. (php@8.1 only)
     */
    // public function setEol(string $eol): static
    // {
    //     $this->eol = $eol;

    //     return $this;
    // }
    
    // public function getEol(): string
    // {
    //     return $this->eol;
    // }

    protected function putRow(array $values): void
    {
        $this->file->fputcsv(
            array_values($values),
            $this->separator,
            $this->enclosure,
            $this->escape,
            // $this->eol (php@8.1 only)
        );
    }
}
