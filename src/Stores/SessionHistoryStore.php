<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Stores;

use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use Illuminate\Contracts\Session\Session;

class SessionHistoryStore implements HistoryStore
{
    public function __construct(
        private readonly Session $session,
        private readonly string $prefix = 'history_engine:'
    ) {
    }

    public function load(string $key): array
    {
        return (array) $this->session->get($key, []);
    }

    public function save(string $key, array $data): void
    {
        $this->session->put($key, $data);
    }

    public function clear(string $key): void
    {
        $this->session->forget($key);
    }
}
