<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Generator;
use Jdefez\LaravelCsv\Csv\Reader;
use Jdefez\LaravelCsv\Tests\TestCase;

class CsvReaderTest extends TestCase
{
    // todo: test encoding

    /** @test */
    public function reader_read_method_returns_a_generator()
    {
        $generator = Reader::fake([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->read();

        $this->assertInstanceOf(Generator::class, $generator);
    }

    /** @test */
    public function it_can_map_data()
    {
        $generator = Reader::fake([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->keyByColumnName()
            ->read(fn ($row) => (object) $row);

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
        }
    }

    /** @test */
    public function it_skips_headings()
    {
        $generator = $generator = Reader::fake([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->keyByColumnName()
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
        $generator = $generator = Reader::fake([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->withHeadings()
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
        $generator = $generator = Reader::fake([
            'column name;count',
            'foo;1',
            'bar;2',
            'baz;3',
        ])->keyByColumnName()
            ->read();

        foreach ($generator as $row) {
            $this->assertArrayHasKey('column_name', $row);
            $this->assertArrayHasKey('count', $row);
        }
    }
}
