<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine;

use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use AlexKassel\HistoryEngine\Exceptions\HistoryEngineException;

class Engine
{
    public function __construct(
        private readonly HistoryStore $store,
        private readonly string $prefix,
        private string $scope = 'default',
    ) {
    }

    public function scope(): string
    {
        return $this->scope;
    }

    public function record(string $item): void
    {
        $data = $this->load();
        $items = $data['items'];
        $pointer = $data['pointer'];

        $last = $items[$pointer] ?? null;
        if ($pointer + 1 === count($items) && $last !== $item) {
            $items[] = $item;
            $pointer = count($items) - 1;
        } elseif ($pointer < count($items) - 1) {
            $tail = array_slice($items, 0, $pointer + 1);
            if ((end($tail) ?: null) !== $item) {
                $tail[] = $item;
            }
            $items = $tail;
            $pointer = count($items) - 1;
        }

        $this->save($items, $pointer);
    }

    /** @return array<int, string> */
    public function getAll(): array
    {
        return $this->load()['items'];
    }

    public function getPointer(): int
    {
        return $this->load()['pointer'];
    }

    public function getCurrent(): ?string
    {
        $data = $this->load();
        return $data['items'][$data['pointer']] ?? null;
    }

    public function clear(): void
    {
        $this->save([], -1);
    }

    public function goToStart(): ?string
    {
        $items = $this->getAll();
        if ($items === []) {
            return null;
        }
        $this->save($items, 0);
        return $items[0];
    }

    public function goToEnd(): ?string
    {
        $items = $this->getAll();
        if ($items === []) {
            return null;
        }
        $index = count($items) - 1;
        $this->save($items, $index);
        return $items[$index];
    }

    public function stepBack(int $steps = 1): ?string
    {
        $data = $this->load();
        if ($data['items'] === []) {
            return null;
        }
        $index = max(0, $data['pointer'] - max(1, $steps));
        $this->save($data['items'], $index);
        return $data['items'][$index];
    }

    public function stepForward(int $steps = 1): ?string
    {
        $data = $this->load();
        if ($data['items'] === []) {
            return null;
        }
        $index = min(count($data['items']) - 1, $data['pointer'] + max(1, $steps));
        $this->save($data['items'], $index);
        return $data['items'][$index];
    }

    public function goToIndex(int $index): ?string
    {
        $data = $this->load();
        if ($data['items'] === []) {
            return null;
        }
        if ($index < 0 || $index >= count($data['items'])) {
            throw new HistoryEngineException("Index out of range: {$index}");
        }
        $this->save($data['items'], $index);
        return $data['items'][$index];
    }

    /** @return array{items: array<int, string>, pointer: int} */
    private function load(): array
    {
        $payload = $this->store->load($this->storageKey());

        $items = $payload['items'] ?? [];
        $pointer = is_int($payload['pointer'] ?? null) ? $payload['pointer'] : -1;

        if ($pointer >= count($items)) {
            $pointer = count($items) - 1;
        }

        return ['items' => $items, 'pointer' => $pointer];
    }

    private function save(array $items, int $pointer): void
    {
        $this->store->save($this->storageKey(), [
            'items' => array_values($items),
            'pointer' => $pointer,
        ]);
    }

    private function storageKey(): string
    {
        return $this->prefix . $this->scope;
    }
}
