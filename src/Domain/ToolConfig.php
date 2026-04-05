<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class ToolConfig
{
    public function __construct(
        public readonly string $name,
        public readonly array $command = [],
        public readonly array $args = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'args' => $this->args,
        ];
    }
}
