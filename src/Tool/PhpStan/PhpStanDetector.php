<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpStan;

use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpStanDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpstan';
    }

    public function detect(string $workingDirectory): ?ToolConfig
    {
        $hasNeon = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpstan.neon')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpstan.neon.dist');

        if (!$hasNeon) {
            return null;
        }

        return new ToolConfig(
            name: $this->name(),
            command: ['vendor/bin/phpstan'],
            args: ['analyse', '--error-format=json']
        );
    }
}
