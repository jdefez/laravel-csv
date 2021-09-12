<?php

namespace Jdefez\LaravelCsv\Tests;

use Jdefez\LaravelCsv\CsvServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            CsvServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
    }
}
