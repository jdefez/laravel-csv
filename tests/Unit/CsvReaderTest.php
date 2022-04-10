<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Generator;
use InvalidArgumentException;
use Jdefez\LaravelCsv\Csv\Reader;
use Jdefez\LaravelCsv\Tests\TestCase;
use SplFileObject;
use SplTempFileObject;

class CsvReaderTest extends TestCase
{
    private Reader $reader;

    private array $lines;

    public function setUp(): void
    {
        parent::setUp();

        $this->lines = [
            'column name*;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ];

        $this->reader = Reader::fake($this->lines);
    }

    /** @test */
    public function it_returns_an_instance_of_Reader(): void
    {
        $reader = new Reader(new SplTempFileObject());
        $this->assertInstanceOf(Reader::class, $reader);
    }

    /** @test */
    public function reader_read_method_returns_a_generator(): void
    {
        $this->assertInstanceOf(Generator::class, $this->reader->read());
    }

    /** @test */
    public function it_can_read_a_given_file(): void
    {
        $file = new SplFileObject($this->stub_path('sample.csv'), 'r');
        $reader = Reader::setFile($file);
        
        $this->assertEquals('sample.csv', $reader->file->getFileInfo()->getBasename());
    }

    /** @test */
    public function it_can_read_a_file(): void
    {
        $lines = [];
        $this->reader->withoutHeadings();

        foreach ($this->reader->read() as $line) {
            array_push($lines, $line);
        }

        $this->assertEqualsCanonicalizing(
            array_map(fn ($item) => explode(';', $item), $this->lines),
            $lines
        );
    }

    /** @test **/
    public function it_can_use_specific_seperator(): void
    {
        $this->reader->setSeparator('|');

        $this->assertEquals('|', $this->reader->getSeparator());
    }

    /** @test */
    public function it_can_use_specific_enclosure(): void
    {
        $this->reader->setEnclosure('"');

        $this->assertEquals('"', $this->reader->getEnclosure());
    }

    /** @test */
    public function it_can_use_specific_escape(): void
    {
        $this->reader->setEscape('*');

        $this->assertEquals('*', $this->reader->getEscape());
    }

    /** @test */
    public function it_can_read_the_same_file_twice(): void
    {
        $reader = $this->reader
            ->keyByColumnName()
            ->toObject();

        $collected = [];
        foreach ($reader->read() as $line) {
            array_push($collected, $line);
        }

        foreach ($reader->read() as $line) {
            array_push($collected, $line);
        }

        $this->assertEquals(6, count($collected));
    }

    /** @test */
    public function it_handles_a_callback_to_map_the_lines(): void
    {
        // maping to stdClass
        $generator = $this->reader
            ->read(fn ($row) => (object) $row);

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
        }
    }

    /** @test */
    public function it_skips_headings_by_default(): void
    {
        $count = 0;
        foreach ($this->reader->read() as $row) {
            $this->assertCount(2, $row);
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_headings_when_requested(): void
    {
        $generator = $this->reader
            ->withHeadings()
            ->read();

        $count = 0;
        foreach ($generator as $row) {
            $this->assertCount(2, $row);
            $count++;
        }
        $this->assertEquals(4, $count);
    }

    /** @test */
    public function results_are_keyed_by_column_name(): void
    {
        $generator = $this->reader
            ->keyByColumnName()
            ->read();

        foreach ($generator as $row) {
            $this->assertArrayHasKey('column_name', $row);
            $this->assertArrayHasKey('count', $row);
        }
    }

    /** @test */
    public function it_results_instances_of_std_class_when_toObject_is_used(): void
    {
        $generator = $this->reader
            ->toObject()
            ->read();

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
            $this->assertTrue(property_exists($row, 'column_name'));
            $this->assertTrue(property_exists($row, 'count'));
        }
    }

    /** @test */
    public function rows_should_not_be_encoded_the_requested_encoding_matches_the_lines_encoding(): void
    {
        $reader = Reader::fake([
            'name;count',
            'féé;1',
        ])->setToEncoding('UTF-8')
          ->keyByColumnName();

        foreach ($reader->read() as $row) {
            $this->assertEquals('féé', $row['name']);
        }
    }

    /** @test */
    public function it_can_set_the_encoding_to_use(): void
    {
        $this->reader->setToEncoding('UTF-16');

        $this->assertEqualsCanonicalizing(
            ['UTF-16', 'UTF-8', 'ISO-8859-15', 'ISO-8859-1'],
            $this->reader->getSearchEncodings()
        );
    }

    /** @test */
    public function it_can_set_the_encodings_to_search_for(): void
    {
        $this->reader->setSearchEncodings(['UTF-16']);

        $this->assertEqualsCanonicalizing(
            ['UTF-16'],
            $this->reader->getSearchEncodings()
        );
    }

    /** @test */
    public function it_converts_iso_859_1_to_utf_8(): void
    {
        $reader = new reader(new SplFileObject($this->stub_path('iso-859-1.csv'), 'r'));

        $generator = $reader->setToEncoding('UTF-8')
            ->keyByColumnName()
            ->read();

        foreach ($generator as $row) {
            $message = 'Keys found: ' . implode('; ', array_keys($row));

            $this->assertArrayHasKey('prenom', $row, $message);
            $this->assertArrayHasKey('nom_d_usage', $row, $message);

            $this->assertEquals('clémentine', $row['prenom']);
        }
    }

    /** @test */
    public function it_throws_if_headings_count_missmatches_rows_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $reader = Reader::fake([
            'column name*;count;erroneus collumn',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->keyByColumnName()
            ->read();

        $reader->next();
    }
}
