<?php

namespace Jdefez\LaravelCsv;

use Illuminate\Support\ServiceProvider;

class CsvServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Csvable::class, Csv::class);
    }
}
