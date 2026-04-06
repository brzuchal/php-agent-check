<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class ToolConfig
{
    public function __construct(
        public string $name,
        public array $command = [],
        public array $args = []
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
