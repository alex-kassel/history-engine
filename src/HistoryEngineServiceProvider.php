<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HistoryEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/history-engine.php', 'history-engine');

        $this->app->singleton(HistoryManager::class, function (Application $app) {
            return new HistoryManager($app, $app['config']['history-engine'] ?? []);
        });

        $this->app->alias(HistoryManager::class, 'history-engine');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/history-engine.php' => config_path('history-engine.php'),
        ], 'history-engine-config');
    }
}
