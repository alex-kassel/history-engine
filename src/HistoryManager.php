<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine;

use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use AlexKassel\HistoryEngine\Stores\CacheHistoryStore;
use AlexKassel\HistoryEngine\Stores\RedisHistoryStore;
use AlexKassel\HistoryEngine\Stores\SessionHistoryStore;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class HistoryManager
{
    protected Application $app;

    /** @var array<string, mixed> */
    protected array $config;

    /** @var array<string, Closure> */
    protected array $customCreators = [];

    public function __construct(Application $app, array $config = [])
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function engine(string $scope = 'default'): Engine
    {
        return new Engine($this->driver(), $this->prefix(), $scope);
    }

    public function driver(?string $name = null): HistoryStore
    {
        $name = $name ?? ($this->config['default'] ?? 'session');

        if (isset($this->customCreators[$name])) {
            /** @var Closure $creator */
            $creator = $this->customCreators[$name];
            return $creator($this->app, $this->config['drivers'][$name] ?? []);
        }

        return match ($name) {
            'session' => $this->createSessionDriver(),
            'cache' => $this->createCacheDriver(),
            'redis' => $this->createRedisDriver(),
            default => throw new InvalidArgumentException("Unsupported history driver [{$name}]")
        };
    }

    public function extend(string $name, Closure $creator): void
    {
        $this->customCreators[$name] = $creator;
    }

    protected function createSessionDriver(): HistoryStore
    {
        $prefix = $this->prefix();

        return $this->app->make(SessionHistoryStore::class, [
            'session' => $this->app->make(\Illuminate\Contracts\Session\Session::class),
            'prefix' => $prefix,
        ]);
    }

    protected function createCacheDriver(): HistoryStore
    {
        $cfg = $this->config['drivers']['cache'] ?? [];
        $prefix = $this->prefix();

        return $this->app->make(CacheHistoryStore::class, [
            'cache' => $this->app->make(\Illuminate\Contracts\Cache\Factory::class),
            'storeName' => $cfg['store'] ?? null,
            'ttl' => $cfg['ttl'] ?? null,
            'prefix' => $prefix,
        ]);
    }

    protected function createRedisDriver(): HistoryStore
    {
        $cfg = $this->config['drivers']['redis'] ?? [];
        $prefix = $this->prefix();

        return $this->app->make(RedisHistoryStore::class, [
            'redis' => $this->app->make(\Illuminate\Contracts\Redis\Factory::class),
            'connectionName' => $cfg['connection'] ?? null,
            'ttl' => $cfg['ttl'] ?? null,
            'prefix' => $prefix,
        ]);
    }

    protected function prefix(): string
    {
        return (string) ($this->config['prefix'] ?? 'history_engine:');
    }
}
