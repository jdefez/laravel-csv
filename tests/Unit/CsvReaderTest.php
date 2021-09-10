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
        /** @var MockInterface */
        $file = Mockery::mock(SplFileObject::class, [], ['php://memory']);

        $file->shouldReceive('setFlags')
            ->once();

        $file->shouldReceive('fgetcsv')
            ->times(2)
            ->with(';', '"', '\\')
            ->andReturn(['foo', '1'], ['bar', '2']);

        $file->shouldReceive('eof')
            ->times(3)
            ->andReturn(false, false, true);

        $generator = CsvReader::setFile($file)
            ->read();

        $this->assertInstanceOf(Generator::class, $generator);

        foreach ($generator as $row) {
            dump($row);
            $this->assertEquals(2, count($row));
        }
    }
}
