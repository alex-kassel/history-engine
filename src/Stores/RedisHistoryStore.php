<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Stores;

use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use Illuminate\Contracts\Redis\Factory as RedisFactory;

class RedisHistoryStore implements HistoryStore
{
    private \Illuminate\Redis\Connections\Connection $connection;
    private ?int $ttl;

    public function __construct(
        RedisFactory $redis,
        ?string $connectionName = null,
        ?int $ttl = null,
        private readonly string $prefix = 'history_engine:'
    ) {
        $this->connection = $redis->connection($connectionName);
        $this->ttl = $ttl;
    }

    public function load(string $key): array
    {
        $raw = $this->connection->get($key);
        if (!is_string($raw)) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function save(string $key, array $data): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($this->ttl) {
            $this->connection->setex($key, $this->ttl, $payload);
        } else {
            $this->connection->set($key, $payload);
        }
    }

    public function clear(string $key): void
    {
        $this->connection->del($key);
    }
}
