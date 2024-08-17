<?php

namespace App\Semaphore\Facades;

use App\Semaphore\Client;

/**
 * @method \Humans\Semaphore\Message message()
 */
class Semaphore extends \Illuminate\Support\Facades\Facade
{
    public static function getFacadeAccessor()
    {
        return Client::class;
    }
}