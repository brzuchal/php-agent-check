<?php

namespace Brzuchal\PhpAgentCheck\Report;

class Issue implements \JsonSerializable
{
    public function __construct(
        public readonly string $type,
        public readonly string $tool,
        public readonly Severity $severity,
        public readonly string $message,
        public readonly ?string $file = null,
        public readonly ?int $line = null,
        public readonly ?string $test = null,
        public readonly ?string $code = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'tool' => $this->tool,
            'severity' => $this->severity->value,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'test' => $this->test,
            'code' => $this->code,
        ];
    }
}
