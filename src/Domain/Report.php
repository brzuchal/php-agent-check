<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class Report implements \JsonSerializable
{
    public function __construct(
        public readonly ToolStatus $status,
        /** @var list<CheckResult> */
        public readonly array $tools
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
