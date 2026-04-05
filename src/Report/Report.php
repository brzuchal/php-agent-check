<?php

namespace Brzuchal\PhpAgentCheck\Report;

class Report implements \JsonSerializable
{
    public function __construct(
        public Status $status = Status::Passed,
        /** @var ToolResult[] */
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
