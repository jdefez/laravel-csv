<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Illuminate\Support\Collection;
use Jdefez\LaravelCsv\Csv\CsvWritable;
use Jdefez\LaravelCsv\Facades\Csv;
use Jdefez\LaravelCsv\Tests\TestCase;

class CsvWriterTest extends TestCase
{
    private CsvWritable $writer;

    private Collection $lines;

    public function setUp(): void
    {
        parent::setUp();

        $this->lines = collect([
            ['name' => 'foo', 'count' => 1],
            ['name' => 'bar', 'count' => 2],
            ['name' => 'baz', 'count' => 3],
        ]);

        $this->writer = Csv::fakeWriter()
            ->setData($this->lines);
    }

    /** @test */
    public function it_generates_a_csv_file()
    {
        $this->writer->write();

        $results = $this->lines->toArray();
        foreach ($this->writer->file as $line) {
            $expected = implode(';', array_shift($results)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }

    /** @test */
    public function it_maps_data()
    {
        $this->writer->write(fn ($item) => [
            $item['name'],
            $item['count'] * 2
        ]);

        $results = [
            ['foo', 2],
            ['bar', 4],
            ['baz', 6],
        ];
        foreach ($this->writer->file as $line) {
            $expected = implode(';', array_shift($results)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }

    /** @test */
    public function it_sets_column_headings()
    {
        $columns = ['column', 'count'];
        $this->writer->setColumns($columns)->write();

        $results = $this->lines->toArray();
        array_unshift($results, $columns);
        foreach ($this->writer->file as $line) {
            $expected = implode(';', array_shift($results)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }
}
