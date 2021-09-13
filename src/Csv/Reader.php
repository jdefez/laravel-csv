<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SplFileObject;
use SplTempFileObject;

class Reader implements Readable, CsvReadable
{
    public SplFileObject|SplTempFileObject $file;

    private string $delimiter = ';';

    private string $enclosure = ' ';

    private ?string $escape = '\\';

    private bool $skip_headings = true;

    private bool $key_by_column_name = false;

    private ?array $headings = null;

    private ?string $to_encoding = null;

    private array $search_encodings = ['ISO-8859-1', 'ISO-8859-15', 'UTF-8'];

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

    public static function fake(array $lines, ?int $maxMemory = null): CsvReadable
    {
        $file = new SplTempFileObject($maxMemory);
        $file->fwrite(implode(PHP_EOL, $lines));
        $file->rewind();

        return new self($file);
    }

    public static function setFile(SplFileObject $file): CsvReadable
    {
        return new self($file);
    }

    public function setToEncoding(string $to_encoding): CsvReadable
    {
        $this->to_encoding = $to_encoding;

        return $this;
    }

    /**
     * @var $encodings array
     *
     * Must include the encoding that will be used to fix
     * the current file
     *
     */
    public function setSearchEncodings(array $encodings)
    {
        $this->search_encodings = $encodings;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function read(?callable $callback = null): Generator
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

    public function withHeadings(): CsvReadable
    {
        $this->skip_headings = false;

        return $this;
    }

    public function keyByColumnName(): CsvReadable
    {
        $this->key_by_column_name = true;

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
        if ($index === 1 && $this->key_by_column_name) {
            $this->setHeadings($row);
        }

        return $this->mapFields($row);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function mapFields(array $row): array
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

            return array_combine($this->headings, $row);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function handleFixEncoding(array $row): array
    {
        if (!in_array($this->to_encoding, $this->search_encodings)) {
            throw new InvalidArgumentException(
                'Reader::$to_encoding must be part of Reader::$search_encodings'
            );
        }

        $from_encoding = mb_detect_encoding(
            $this->file->current()[0],
            $this->search_encodings
        );

        if ($from_encoding
            && $from_encoding !== $this->to_encoding
        ) {
            return array_map(
                fn ($item) => $this->encode($item, $from_encoding),
                $row
            );
        }

        return $row;
    }

    public function encode(?string $string = null, string $from_encoding)
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
