<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class CheckExecution
{
    public function __construct(
        public array $command,
        public string $workingDirectory,
        public array $environmentVariables = [],
        public int $timeout = 300
    ) {
    }
}
