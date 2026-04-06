<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class CheckExecutionResult
{
    public function __construct(
        public int $exitCode,
        public string $stdout,
        public string $stderr
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->exitCode === 0;
    }
}
