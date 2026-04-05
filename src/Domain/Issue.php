<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class Issue implements \JsonSerializable
{
    public function __construct(
        public string $type,
        public string $tool,
        public Severity $severity,
        public string $message,
        public string|null $file = null,
        public int|null $line = null,
        public string|null $test = null,
        public string|null $code = null
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
