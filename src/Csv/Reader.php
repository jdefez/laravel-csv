<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SplFileObject;

class Reader implements Readable, CsvReadable
{
    public SplFileObject $file;

    private string $delimiter = ';';

    private string $enclosure = ' ';

    private ?string $escape = '\\';

    private bool $skip_headings = true;

    private bool $map_fields_with_headings = false;

    private ?array $headings = null;

    // Must include the target encoding: 'UTF-8'
    private array $encodings = ['ISO-8859-1', 'ISO-8859-15', 'UTF-8'];

    public function __construct(SplFileObject $file)
    {
        $this->file = $file;
        $this->file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );
    }

    public static function setFile(SplFileObject $file): CsvReadable
    {
        return new self($file);
    }

    public function setEncodings(array $encodings)
    {
        $this->encodings = $encodings;
    }

    public function read(): Generator
    {
        $index = 0;
        while (! $this->file->eof()) {
            ++$index;

            $row = $this->file->fgetcsv(
                $this->delimiter,
                $this->enclosure,
                $this->escape
            );

            if (! is_array($row)) {
                continue;
            }

            $row = $this->fixEncoding($row);

            $row = $this->handleMappingSetting($index, $row);

            if ($this->skip_headings && $index === 1) {
                continue;
            }

            yield $row;
        }
    }

    public function toCollection(?callable $callback = null): Collection
    {
        $collection = collect();

        foreach ($this->read() as $row) {
            $collection->push(is_callable($callback) ? $callback($row) : $row);
        }

        return $collection;
    }

    public function withHeadings(): CsvReadable
    {
        $this->skip_headings = false;

        return $this;
    }

    public function mapFieldsWithHeadings(): CsvReadable
    {
        if ($this->skip_headings) {
            $this->skip_headings = false;
        }

        $this->map_fields_with_headings = true;

        return $this;
    }

    public function setDelimiter(string $delimiter): CsvReadable
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    private function setHeadings(array $row)
    {
        $this->headings = array_map(
            fn ($item) => (string) Str::of($item)->lower()->snake()->ascii(),
            $row
        );
    }

    private function handleMappingSetting(int $index, array $row): array
    {
        if ($index === 1 && $this->map_fields_with_headings) {
            $this->setHeadings($row);
        }

        return $this->mapFields($row);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function mapFields(array $row): array
    {
        if (! $this->map_fields_with_headings) {
            return $row;
        }

        if ($this->headings) {
            throw_unless(
                array_combine($this->headings, $row),
                InvalidArgumentException::class,
                'Reader::mapFields failed'
            );

            return array_combine($this->headings, $row);
        }
    }

    private function fixEncoding(array $row): array
    {
        if (!self::isUtf8($this->file->current(), $this->encodings)) {
            return array_map(fn ($item) => self::toUtf8($item), $row);
        }

        return $row;
    }

    public static function isUtf8(
        ?string $string = null,
        ?array $encodings = ['ISO-8859-1', 'UTF-8']
    ): bool {
        if ($string) {
            return mb_detect_encoding($string, $encodings) === 'UTF-8';
        }

        return true;
    }

    public static function toUtf8(
        ?string $string = null,
        ?string $fromEncoding = 'ISO-8859-1'
    ): string {
        if ($string) {
            return mb_convert_encoding($string, 'UTF-8', $fromEncoding);
        }

        return $string;
    }
}
