<?php

declare(strict_types=1);

namespace AlexKassel\HistoryEngine\Adapters;

use AlexKassel\HistoryEngine\Engine;
use AlexKassel\HistoryEngine\Exceptions\HistoryEngineException;

final class StringCommandAdapter
{
    // Define command constants
    private const CMD_START = '<<';
    private const CMD_END = '>>';
    private const CMD_BACK = '<';
    private const CMD_FORWARD = '>';

    private const CMD_INDEX = '<>(%d)';
    private const CMD_BACK_N = '<(%d)';
    private const CMD_FORWARD_N = '>(%d)';

    public function __construct(private readonly Engine $engine)
    {
    }

    public function apply(string $command): ?string
    {
        $command = trim($command);

        // Simple command map
        $simpleCommands = [
            self::CMD_START   => 'goToStart',
            self::CMD_END     => 'goToEnd',
            self::CMD_BACK    => 'stepBack',
            self::CMD_FORWARD => 'stepForward',
        ];

        if (array_key_exists($command, $simpleCommands)) {
            return $this->{$simpleCommands[$command]}();
        }

        // Regex command map
        $regexCommands = [
            self::CMD_INDEX => 'goToIndex',
            self::CMD_BACK_N => 'stepBack',
            self::CMD_FORWARD_N => 'stepForward',
        ];

        foreach ($regexCommands as $regex => $method) {
            if (preg_match('/^' . $regex . '$/', $command, $m)) {
                return $this->{$method}((int) $m[1]);
            }
        }

        throw new HistoryEngineException("Unsupported command [{$command}]");
    }
}
