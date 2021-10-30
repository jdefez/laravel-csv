<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Generator;
use Jdefez\LaravelCsv\Csv\Readable;
use Jdefez\LaravelCsv\Facades\Csv;
use Jdefez\LaravelCsv\Tests\TestCase;
use SplFileObject;
use SplTempFileObject;

class CsvReaderTest extends TestCase
{
    private Readable $reader;

    public function setUp(): void
    {
        parent::setUp();

        $this->reader = Csv::fakeReader([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ]);
    }

    /** @test */
    public function it_returns_an_instance_of_Readable()
    {
        $reader = Csv::reader(new SplTempFileObject());
        $this->assertInstanceOf(Readable::class, $reader);
    }

    /** @test */
    public function reader_read_method_returns_a_generator()
    {
        $this->assertInstanceOf(Generator::class, $this->reader->read());
    }

    /** @test */
    public function it_can_read_the_file()
    {
        $count = 0;
        foreach ($this->reader->read() as $line) {
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_can_read_the_same_file_twice()
    {
        $reader = $this->reader
            ->keyByColumnName()
            ->toObject();

        $count = 0;
        foreach ($reader->read() as $line) {
            $count++;
        }

        foreach ($reader->read() as $line) {
            $count++;
        }

        $this->assertEquals(6, $count);
    }

    /** @test */
    public function it_handles_a_callback_to_map_the_lines()
    {
        // maping to stdClass
        $generator = $this->reader
            ->read(fn ($row) => (object) $row);

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
        }
    }

    /** @test */
    public function it_skips_headings_by_default()
    {
        $count = 0;
        foreach ($this->reader->read() as $row) {
            $this->assertCount(2, $row);
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_headings_when_requested()
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
    public function results_are_keyed_by_column_name()
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
    public function it_results_instances_of_std_class_when_toObject_is_used()
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
    public function rows_should_not_be_encoded_the_requested_encoding_matches_the_lines_encoding()
    {
        $reader = Csv::fakeReader([
            'name;count',
            'féé;1',
        ])->setToEncoding('UTF-8')
          ->keyByColumnName();

        foreach ($reader->read() as $row) {
            $this->assertEquals('féé', $row['name']);
        }
    }

    /** @test */
    public function it_converts_iso_859_1_to_utf_8()
    {
        // todo: save stub as utf-8 and encode to iso-859-1 after loading
        $reader = Csv::reader(
            new SplFileObject(__DIR__ . '/../Stubs/iso-859-1.csv', 'r')
        );

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
}
