<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class CheckContext
{
    public function __construct(
        public readonly array $config,
        public readonly string $workingDirectory
    ) {
    }
}
