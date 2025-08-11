<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Contracts;

interface HistoryStore
{
    /**
     * Load payload for the given key.
     *
     * @return array{items?: array<int, string>, pointer?: int}
     */
    public function load(string $key): array;

    /**
     * Persist payload for the given key.
     *
     * @param array{items: array<int, string>, pointer: int} $data
     */
    public function save(string $key, array $data): void;

    public function clear(string $key): void;
}
