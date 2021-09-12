<?php

namespace Jdefez\LaravelCsv\Facades;

use Illuminate\Support\Facades\Facade;
use Jdefez\LaravelCsv\Csvable;

class Csv extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Csvable::class;
    }
}
