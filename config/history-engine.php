<?php

declare(strict_types=1);

return [
    // session | cache | redis
    'default' => env('HISTORY_ENGINE_DRIVER', 'session'),

    // Key prefix used when storing data
    'prefix' => env('HISTORY_ENGINE_PREFIX', 'history_engine:'),

    'drivers' => [
        'session' => [
            'class' => \AlexKassel\HistoryEngine\Stores\SessionHistoryStore::class,
        ],

        'cache' => [
            'class' => \AlexKassel\HistoryEngine\Stores\CacheHistoryStore::class,
            'store' => env('HISTORY_ENGINE_CACHE_STORE', null),
            'ttl' => env('HISTORY_ENGINE_TTL', null), // seconds
        ],

        'redis' => [
            'class' => \AlexKassel\HistoryEngine\Stores\RedisHistoryStore::class,
            'connection' => env('HISTORY_ENGINE_REDIS_CONNECTION', null),
            'ttl' => env('HISTORY_ENGINE_TTL', null), // seconds
        ],
    ],
];
