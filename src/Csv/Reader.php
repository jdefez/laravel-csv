<?php

namespace Jdefez\LaravelCsv\Csv;

use Generator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SplFileObject;
use stdClass;

class Reader implements Readable
{
    public SplFileObject $file;

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

        $file->setCsvControl(';');

        $this->file = $file;
    }

    /**
     * Returns a generator that can be used to iterate over the file rows. When
     * provided the callable is applyed to each row.
     *
     * @throws InvalidArgumentException
     */
    public function read(?callable $callback = null): Generator
    {
        $index = 0;

        foreach ($this->file as $line) {
            $index++;

            if (! is_array($line)) {
                continue;
            }

            if (! is_null($this->to_encoding)) {
                $line = $this->handleFixEncoding($line);
            }

            $line = $this->handleMappingSetting($index, $line);

            if ($this->skip_headings && $index === 1) {
                continue;
            }

            if ($callback && is_callable($callback)) {
                $line = $callback($line);
            }

            yield $line;
        }
    }

    /**
     * Returns a new Reader instanciated with a SplTempFileObject
     */
    public static function fake(array $lines, ?int $maxMemory = null): self
    {
        return new static(File::fake($lines, $maxMemory));
    }

    /**
     * Returns a new instance with the given SplFileObject
     */
    public static function setFile(SplFileObject $file): self
    {
        return new static($file);
    }

    /**
     * Sets the encoding that will be used to encode the rows to when needed
     */
    public function setToEncoding(string $to_encoding): self
    {
        $this->to_encoding = $to_encoding;

        $this->addEncodingToSearchEncodings($to_encoding);

        return $this;
    }

    /**
     * An ordered array of encodings to check when determing the actual row
     * encoding. It must include the encoding that will be used to fix the
     * current file.
     */
    public function setSearchEncodings(array $encodings): self
    {
        $this->search_encodings = $encodings;

        return $this;
    }

    /**
     * By default the first row is skipped. Use this method to include the
     * first row.
     */
    public function withHeadings(): self
    {
        $this->skip_headings = false;

        return $this;
    }

    /**
     * The array of cells returned is keyed with the corresponding columns names.
     */
    public function keyByColumnName(): self
    {
        $this->key_by_column_name = true;

        return $this;
    }

    /**
     * Converts each rows to an object. Its properties are the snake cased
     * column names.
     */
    public function toObject(): self
    {
        $this->key_by_column_name = true;
        $this->to_object = true;

        return $this;
    }

    /**
     * Sets the delimiter, enclosure and escape to be used to parse the file.
     */
    public function setDelimiter(
        string $separator = ",",
        string $enclosure = "\"",
        string $escape = "\\"
    ): self {
        $this->file->setCsvControl($separator, $enclosure, $escape);

        return $this;
    }

    protected function setHeadings(array $row)
    {
        $this->headings = array_map(fn ($item) => $this->snake($item), $row);
    }

    protected function snake(string $string): string
    {
        return (string) Str::of($string)
            ->ascii()
            ->replaceMatches('/[^[:alnum:][:space:]]/', ' ')
            ->lower()
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

    protected function handleFixEncoding(array $row): array
    {
        $from_encoding = $this->currentEncoding(join('', $this->file->current()));

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

    private function addEncodingToSearchEncodings(string $to_encoding): void
    {
        if (!in_array($to_encoding, $this->search_encodings)) {
            $this->search_encodings = array_unshift(
                $this->search_encodings,
                $to_encoding
            );
        }
    }
}
