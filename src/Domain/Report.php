<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class Report implements \JsonSerializable
{
    public function __construct(
        public ToolStatus $status,
        /** @var list<CheckResult> */
        public array $tools
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status->value,
            'tools' => $this->tools,
        ];
    }
}
