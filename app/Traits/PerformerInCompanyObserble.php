<?php

namespace App\Traits;

use App\Observers\PerformerInCompanyObserver;

trait PerformerInCompanyObserble
{
    public static function bootPerformerInCompanyObserble()
    {
        self::observe(PerformerInCompanyObserver::class);
    }
}