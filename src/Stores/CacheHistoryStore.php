<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Stores;

use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CacheHistoryStore implements HistoryStore
{
    private CacheRepository $cache;
    private ?int $ttl;

    public function __construct(
        CacheFactory $cache,
        ?string $storeName = null,
        ?int $ttl = null,
        private readonly string $prefix = 'history_engine:'
    ) {
        $this->cache = $storeName ? $cache->store($storeName) : $cache->store();
        $this->ttl = $ttl;
    }

    public function load(string $key): array
    {
        return (array) $this->cache->get($key, []);
    }

    public function save(string $key, array $data): void
    {
        if ($this->ttl) {
            $this->cache->put($key, $data, $this->ttl);
        } else {
            $this->cache->forever($key, $data);
        }
    }

    public function clear(string $key): void
    {
        $this->cache->forget($key);
    }
}
