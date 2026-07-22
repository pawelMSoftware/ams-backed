<?php

namespace App\AMS;

use Illuminate\Support\Facades\Facade;

class AMSRedisFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return '\App\AMS\AMSRedis';
    }
}
