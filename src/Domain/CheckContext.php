<?php

namespace Brzuchal\PhpAgentCheck\Domain;

readonly final class CheckContext
{
    public function __construct(
        public ToolConfig $config,
        public string $workingDirectory
    ) {
    }
}
