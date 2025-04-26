<?php

namespace Ammanade\Docs\Facades;

use Ammanade\Docs\Generator;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Ammanade\Docs\Docs
 */
class Docs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Generator::class;
    }
}
