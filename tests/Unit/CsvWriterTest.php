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
    public function it_generates_a_csv_file_from_a_collection()
    {
        $this->writer->write();

        $results = $this->lines->toArray();

        foreach ($this->writer->file as $line) {
            $expected = implode(';', array_shift($results)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }

    /** @test */
    public function it_maps_collection_data()
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

    /** @test */
    public function it_can_put_one_line_to_the_file()
    {
        $delimiter = ',';
        $writer = Csv::fakeWriter()
            ->setDelimiter($delimiter);

        foreach ($this->lines as $line) {
            $writer->put($line);
        }

        $count = 0;
        foreach ($writer->file as $line) {
            $expected = implode($delimiter, $this->lines[$count]) . PHP_EOL;
            $this->assertEquals($expected, $line);

            $count++;
        }

        $this->assertEquals($this->lines->count(), $count);
    }
}
