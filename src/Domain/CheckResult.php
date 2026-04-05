<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class CheckResult implements \JsonSerializable
{
    public function __construct(
        public readonly string $tool,
        public readonly ToolStatus $status,
        /** @var list<Issue> */
        public readonly array $issues = []
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->tool,
            'status' => $this->status->value,
            'issues' => $this->issues,
        ];
    }
}
