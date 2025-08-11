<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Adapters;

use AlexKassel\HistoryEngine\Engine;

final class StringCommandAdapter
{
    public function __construct(private readonly Engine $engine)
    {
    }

    public function apply(string $command): ?string
    {
        return $this->engine->applyCommand($command);
    }
}
