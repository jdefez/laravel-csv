<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Jdefez\LaravelCsv\Csv\Writer;
use Jdefez\LaravelCsv\Tests\TestCase;

class CsvWriterTest extends TestCase
{
    /** @test */
    public function it_generates_a_csv_file()
    {
        $lines = [
            ['foo', 1],
            ['bar', 2],
            ['baz', 3],
        ];

        $writer = Writer::fake()
            ->setData(collect($lines));

        $writer->write();

        foreach ($writer->file as $line) {
            $expected = implode(';', array_shift($lines)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }

    /** @test */
    public function it_maps_data()
    {
        $lines = [
            ['name' => 'foo', 'count' => 1],
            ['name' => 'bar', 'count' => 2],
            ['name' => 'baz', 'count' => 3],
        ];

        $writer = Writer::fake()
            ->setData(collect($lines));

        $writer->write(fn ($item) => [
            $item['name'],
            $item['count'] * 2
        ]);

        $expected_resuls = [
            ['foo', 2],
            ['bar', 4],
            ['baz', 6],
        ];
        foreach ($writer->file as $line) {
            $expected = implode(';', array_shift($expected_resuls)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }

    /** @test */
    public function it_sets_column_headings()
    {
        $lines = [
            ['foo', 1],
            ['bar', 2],
            ['baz', 3],
        ];

        $columns = ['column', 'count'];

        $writer = Writer::fake()
            ->setColumns($columns)
            ->setData(collect($lines));

        $writer->write();

        array_unshift($lines, $columns);
        foreach ($writer->file as $line) {
            $expected = implode(';', array_shift($lines)) . PHP_EOL;
            $this->assertEquals($expected, $line);
        }
    }
}
