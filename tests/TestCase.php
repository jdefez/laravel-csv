<?php

namespace Jdefez\LaravelCsv\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp($app)
    {
    }

    public function stub_path(string $filename): string
    {
        return __DIR__ . '/Stubs/' . $filename;
    }
}
