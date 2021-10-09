<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Generator;
use Jdefez\LaravelCsv\Csv\CsvReadable;
use Jdefez\LaravelCsv\Facades\Csv;
use Jdefez\LaravelCsv\Tests\TestCase;
use SplFileObject;

class CsvReaderTest extends TestCase
{
    private CsvReadable $reader;

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
    public function reader_read_method_returns_a_generator()
    {
        $generator = $this->reader->read();

        $this->assertInstanceOf(Generator::class, $generator);
    }

    /** @test */
    public function it_can_map_data()
    {
        $generator = $this->reader
            ->keyByColumnName()
            ->read(fn ($row) => (object) $row);

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
        }
    }

    /** @test */
    public function it_skips_headings()
    {
        $generator = $this->reader
            ->keyByColumnName()
            ->read();

        $count = 0;
        foreach ($generator as $row) {
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
    public function rows_should_not_be_encoded_if_encoding_used_the_same_the_encoding_requested()
    {
        $generator = Csv::fakeReader([
            'name;count',
            'féé;1',
        ])->setToEncoding('UTF-8')
          ->keyByColumnName()
          ->read();

        foreach ($generator as $row) {
            $this->assertEquals('féé', $row['name']);
        }
    }

    /** @test */
    public function it_converts_iso_859_1_to_utf_8()
    {
        $reader = Csv::reader(
            new SplFileObject(__DIR__ . '/../Stubs/iso-859-1.csv', 'r')
        );

        $generator = $reader->setToEncoding('UTF-8')
            ->keyByColumnName()
            ->read();

        foreach ($generator as $row) {
            $this->assertArrayHasKey('prenom', $row, implode(';', array_keys($row)));
            $this->assertArrayHasKey('nom_d_usage', $row, implode(';', array_keys($row)));
            $this->assertEquals('clémentine', $row['prenom']);
        }
    }
}
