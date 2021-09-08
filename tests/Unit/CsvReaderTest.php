<?php

namespace Jdefez\LaravelCsv\Tests\Unit;

use Jdefez\LaravelCsv\Tests\TestCase;
use \Mockery;

class CsvReaderTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_works()
    {
        // http://docs.mockery.io/en/latest/getting_started/simple_example.html
        $file = Mockery::mock('file');
        $this->assertTrue(true);
    }
}
