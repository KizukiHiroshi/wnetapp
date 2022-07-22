<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Sequence extends Facade 
{
    protected static function getFacadeAccessor()
    {
        return \App\Services\SequenceService::class;
    }
}