<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class Issue implements \JsonSerializable
{
    public function __construct(
        public readonly string $type,
        public readonly string $tool,
        public readonly Severity $severity,
        public readonly string $message,
        public readonly string|null $file = null,
        public readonly int|null $line = null,
        public readonly string|null $test = null,
        public readonly string|null $code = null
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
