<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpStan;

use Brzuchal\PhpAgentCheck\Application\ComposerProject;
use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpStanDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpstan';
    }

    public function detect(string $workingDirectory, ?ComposerProject $composerProject = null): ?ToolConfig
    {
        $hasNeon = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpstan.neon')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpstan.neon.dist');
        $hasPackage = $composerProject?->hasPackage('phpstan/phpstan') ?? false;

        if (!$hasNeon && !$hasPackage) {
            return null;
        }

        $binDir = $composerProject?->getBinDir() ?? 'vendor/bin';

        return new ToolConfig(
            name: $this->name(),
            command: [$binDir . DIRECTORY_SEPARATOR . 'phpstan'],
            args: ['analyse', '--error-format=json']
        );
    }
}
