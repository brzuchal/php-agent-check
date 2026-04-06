<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final readonly class ProfileDefinition
{
    /**
     * @param string[] $tools
     */
    public function __construct(
        public string $name,
        public array $tools
    ) {
    }

    public function toArray(): array
    {
        return [
            'tools' => $this->tools,
        ];
    }
}
