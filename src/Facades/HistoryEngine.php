<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Facades;

use Illuminate\Support\Facades\Facade;
use AlexKassel\HistoryEngine\HistoryManager;

class HistoryEngine extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HistoryManager::class;
    }
}
