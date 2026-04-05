<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class CheckExecution
{
    public function __construct(
        public readonly array $command,
        public readonly string $workingDirectory,
        public readonly array $environmentVariables = [],
        public readonly int $timeout = 300
    ) {
    }
}
