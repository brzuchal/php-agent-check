<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class CheckResult implements \JsonSerializable
{
    public function __construct(
        public string $tool,
        public ToolStatus $status,
        /** @var list<Issue> */
        public array $issues = []
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
