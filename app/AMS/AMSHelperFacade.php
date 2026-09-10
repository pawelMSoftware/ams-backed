<?php

namespace App\AMS;

/**
 * @method static void getStorageNameForDiscAlias(string $filesAlias)
 */
use Illuminate\Support\Facades\Facade;

class AMSHelperFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\AMS\AMSHelper::class;
    }
}
