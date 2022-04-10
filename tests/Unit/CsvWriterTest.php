<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Illuminate\Support\Collection;
use Jdefez\LaravelCsv\Csv\Writer;
use Jdefez\LaravelCsv\Tests\TestCase;
use SplTempFileObject;

class CsvWriterTest extends TestCase
{
    private Writer $writer;

    /** @var Collection<array> */
    private Collection $lines;

    public function setUp(): void
    {
        parent::setUp();

        $this->lines = collect([
            ['name' => 'foo', 'count' => 1],
            ['name' => 'bar', 'count' => 2],
            ['name' => 'baz', 'count' => 3],
        ]);

        $this->writer = Writer::fake()->setData($this->lines);
    }

    /** @test */
    public function it_returns_an_instance_of_Writable()
    {
        $writer = Writer::setFile(new SplTempFileObject());

        $this->assertInstanceOf(Writer::class, $writer);
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
        $writer = Writer::fake()->setSeparator(',');

        foreach ($this->lines as $line) {
            /** @var array $line */
            $writer->put($line);
        }

        $count = 0;
        foreach ($writer->file as $line) {
            /** @var array $line */
            $expected = implode($writer->getSeparator(), $this->lines[$count]) . PHP_EOL;
            $this->assertEquals($expected, $line);

            $count++;
        }

        $this->assertEquals($this->lines->count(), $count);
    }

    /** @test */
    public function it_can_use_specific_separator(): void
    {
        $this->writer->setSeparator('|');

        $this->assertEquals('|', $this->writer->getSeparator());
    }

    /** @test */
    public function it_can_use_specific_escape(): void
    {
        $this->writer->setEscape('|');

        $this->assertEquals('|', $this->writer->getEscape());
    }

    /** @test */
    public function it_can_use_specific_encolsure(): void
    {
        $this->writer->setEnclosure('\'');

        $this->assertEquals('\'', $this->writer->getEnclosure());
    }

    /** @test */
    public function put_method_can_handle_a_callable()
    {
        $writer = Writer::fake()->setSeparator(',');

        foreach ($this->lines as $line) {
            $writer->put(fn () => [
                $line['name'] . ' updated',
                $line['count'] + 1,
            ]);
        }

        $count = 0;
        foreach ($writer->file as $line) {
            $value = $this->lines[$count];

            $expected = sprintf(
                '"%s updated",%d',
                $value['name'],
                $value['count'] + 1
            ) . PHP_EOL;

            $this->assertEquals($expected, $line);

            $count++;
        }

        $this->assertEquals($this->lines->count(), $count);
    }
}
