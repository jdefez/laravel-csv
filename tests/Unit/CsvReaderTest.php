<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Generator;
use Jdefez\LaravelCsv\Csv\Reader;
use Jdefez\LaravelCsv\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use SplFileObject;

class CsvReaderTest extends TestCase
{
    // todo: test encoding

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function read_returns_a_generator()
    {
        $file = $this->generateMock([
            ['column name', 'count'],
            ['foo', '1'],
            ['bar', '2'],
            ['baz', '3'],
        ]);

        $generator = Reader::setFile($file)
            ->read();

        $this->assertInstanceOf(Generator::class, $generator);

        foreach ($generator as $row) {
            $this->assertCount(2, $row);
        }
    }

    /** @test */
    public function it_can_map_data()
    {
        $file = $this->generateMock([
            ['column name', 'count'],
            ['foo', '1'],
            ['bar', '2'],
            ['baz', '3'],
        ]);

        $generator = Reader::setFile($file)
            ->keyByColumnName()
            ->read(fn ($row) => (object) $row);

        foreach ($generator as $row) {
            $this->assertInstanceOf('stdClass', $row);
        }
    }

    /** @test */
    public function it_skips_headings()
    {
        $file = $this->generateMock([
            ['column name', 'count'],
            ['foo', '1'],
            ['bar', '2'],
            ['baz', '3'],
        ]);

        $generator = Reader::setFile($file)
            ->keyByColumnName()
            ->read();

        $count = 0;
        foreach ($generator as $row) {
            $this->assertCount(2, $row);
            $count ++;
        }
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_headings_when_requested()
    {
        $file = $this->generateMock([
            ['column name', 'count'],
            ['foo', '1'],
            ['bar', '2'],
            ['baz', '3'],
        ]);

        $generator = Reader::setFile($file)
            ->withHeadings()
            ->read();

        $count = 0;
        foreach ($generator as $row) {
            $this->assertCount(2, $row);
            $count ++;
        }
        $this->assertEquals(4, $count);
    }

    /** @test */
    public function results_are_keyed_by_column_name()
    {
        $file = $this->generateMock([
            ['column name', 'count'],
            ['foo', '1'],
            ['bar', '2'],
            ['baz', '3'],
        ]);

        $generator = Reader::setFile($file)
            ->keyByColumnName()
            ->read();

        foreach ($generator as $row) {
            $this->assertArrayHasKey('column_name', $row);
            $this->assertArrayHasKey('count', $row);
        }
    }

    private function getEofExpectations(array $lines): array
    {
        $eofExpectation = array_fill(0, count($lines), false);
        $eofExpectation[] = true;

        return $eofExpectation;
    }

    private function generateMock(array $lines): MockInterface
    {
        $eofExpectation = $this->getEofExpectations($lines);

        /** @var MockInterface */
        $file = Mockery::mock(SplFileObject::class, [], ['php://memory']);

        $file->shouldReceive('setFlags')
            ->once();

        $file->shouldReceive('eof')
            ->times(count($eofExpectation))
            ->andReturn(...$eofExpectation);

        $file->shouldReceive('fgetcsv')
            ->times(count($lines))
            ->with(';', ' ', '\\')
            ->andReturn(...$lines);

        // Only reach when try to fix the file encoding
        //$file->shouldReceive('current')
        //->withAnyArgs()
        //->times(count($lines));

        return $file;
    }
}
