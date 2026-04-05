<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class Report implements \JsonSerializable
{
    public function __construct(
        public ToolStatus $status = ToolStatus::Passed,
        /** @var list<CheckResult> */
        public array $tools = []
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
