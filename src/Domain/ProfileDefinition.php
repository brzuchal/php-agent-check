<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class ProfileDefinition
{
    /**
     * @param string[] $tools
     */
    public function __construct(
        public readonly string $name,
        public readonly array $tools
    ) {
    }
}
