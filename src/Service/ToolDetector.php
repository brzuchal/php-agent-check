<?php

namespace Brzuchal\PhpAgentCheck\Service;

use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

interface ToolDetector
{
    public function name(): string;

    public function detect(string $workingDirectory, ?ComposerProject $composerProject = null): ?ToolConfig;
}
