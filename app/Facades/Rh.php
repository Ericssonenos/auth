<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Rh extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Support\RhService::class;
    }
}
