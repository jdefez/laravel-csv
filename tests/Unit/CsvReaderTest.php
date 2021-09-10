<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Jdefez\LaravelCsv\CsvReader;
use Jdefez\LaravelCsv\Tests\TestCase;
use Mockery;
use Generator;
use SplFileObject;

class CsvReaderTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_works()
    {
        $lines = [
            ['foo', '1'], ['bar', '2']
        ];
        $eofExpectation = $this->getEofExpectations($lines);

        /** @var MockInterface */
        $file = Mockery::mock(SplFileObject::class, [], ['php://memory']);

        $file->shouldReceive('setFlags')
            ->once();

        $file->shouldReceive('fgetcsv')
            ->times(count($lines))
            ->with(';', '"', '\\')
            ->andReturn(...$lines);

        $file->shouldReceive('eof')
            ->times(count($eofExpectation))
            ->andReturn(...$eofExpectation);

        $generator = CsvReader::setFile($file)
            ->read();

        $this->assertInstanceOf(Generator::class, $generator);

        //foreach ($generator as $row) {
            //dump($row);
        //}
    }

    private function getEofExpectations(array $lines): array
    {
        $eofExpectation = array_fill(0, count($lines), false);
        $eofExpectation[] = true;

        return $eofExpectation;
    }
}
