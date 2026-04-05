<?php

namespace Brzuchal\PhpAgentCheck\Report;

class ToolResult implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public Status $status,
        /** @var Issue[] */
        public array $issues = []
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status->value,
            'issues' => $this->issues,
        ];
    }
}
