<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SplFileObject;
use stdClass;

class Reader implements Readable, CsvReadable
{
    public SplFileObject $file;

    private string $delimiter = ';';

    private string $enclosure = ' ';

    private ?string $escape = '\\';

    private bool $skip_headings = true;

    private bool $key_by_column_name = false;

    private bool $to_object = false;

    private ?array $headings = null;

    private ?string $to_encoding = null;

    private array $search_encodings = ['UTF-8', 'ISO-8859-15', 'ISO-8859-1'];

    final public function __construct(SplFileObject $file)
    {
        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );

        $this->file = $file;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function read(?callable $callback = null): Generator
    {
        $index = 0;

        while (! $this->file->eof()) {
            $index++;

            $row = $this->file->fgetcsv(
                $this->delimiter,
                $this->enclosure,
                $this->escape
            );

            if (! is_array($row)) {
                continue;
            }

            if (! is_null($this->to_encoding)) {
                $row = $this->handleFixEncoding($row);
            }

            $row = $this->handleMappingSetting($index, $row);

            if ($this->skip_headings && $index === 1) {
                continue;
            }

            if ($callback && is_callable($callback)) {
                $row = $callback($row);
            }

            yield $row;
        }
    }

    public static function fake(array $lines, ?int $maxMemory = null): self
    {
        return new static(File::fake($lines, $maxMemory));
    }

    public static function setFile(SplFileObject $file): self
    {
        return new static($file);
    }

    public function setToEncoding(string $to_encoding): self
    {
        $this->to_encoding = $to_encoding;

        return $this;
    }

    /**
     * Ordered array of encodings to check when determing the actual line encoding
     * It must include the encoding that will be used to fix the current file
     */
    public function setSearchEncodings(array $encodings): self
    {
        $this->search_encodings = $encodings;

        return $this;
    }

    public function withHeadings(): self
    {
        $this->skip_headings = false;

        return $this;
    }

    public function keyByColumnName(): self
    {
        $this->key_by_column_name = true;

        return $this;
    }

    public function toObject(): self
    {
        $this->to_object = true;

        return $this;
    }

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    protected function setHeadings(array $row)
    {
        $this->headings = array_map(fn ($item) => $this->snake($item), $row);
    }

    protected function snake(string $string): string
    {
        return (string) Str::of($string)
            ->replace(['\''], [' ', ' '])
            ->remove([',', ';', '.', '"'])
            ->lower()
            ->ascii()
            ->snake();
    }

    protected function handleMappingSetting(int $index, array $row): array|stdClass
    {
        if ($index === 1 && $this->key_by_column_name) {
            $this->setHeadings($row);
        }

        return $this->mapFields($row);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function mapFields(array $row): array|stdClass
    {
        if (! $this->key_by_column_name) {
            return $row;
        }

        if ($this->headings) {
            throw_unless(
                array_combine($this->headings, $row),
                InvalidArgumentException::class,
                'Reader::mapFields failed'
            );

            $row = array_combine($this->headings, $row);

            if ($this->to_object) {
                $row = (object) $row;
            }

            return $row;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function handleFixEncoding(array $row): array
    {
        if (!in_array($this->to_encoding, $this->search_encodings)) {
            throw new InvalidArgumentException(
                'Reader::$to_encoding must be part of Reader::$search_encodings'
            );
        }

        $from_encoding = $this->currentEncoding($this->file->current()[0]);

        if (! $from_encoding || ! $this->needEncoding($from_encoding)) {
            return $row;
        }

        return $this->encodeRow($row, $from_encoding);
    }

    protected function currentEncoding(string $string): bool|string
    {
        return mb_detect_encoding($string, $this->search_encodings);
    }

    protected function encodeRow(array $row, string $from_encoding): array
    {
        return array_map(fn ($item) => $this->encode($item, $from_encoding), $row);
    }

    protected function needEncoding(string $from_encoding): bool
    {
        return $from_encoding !== $this->to_encoding;
    }

    protected function encode(?string $string = null, string $from_encoding)
    {
        if ($string) {
            return mb_convert_encoding(
                $string,
                $this->to_encoding,
                $from_encoding
            );
        }

        return $string;
    }
}
