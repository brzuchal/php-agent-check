<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpUnit;

use Brzuchal\PhpAgentCheck\Service\ComposerProject;
use Brzuchal\PhpAgentCheck\Service\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpUnitDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpunit';
    }

    public function detect(string $workingDirectory, ?ComposerProject $composerProject = null): ?ToolConfig
    {
        $hasXml = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml.dist');
        $hasPackage = $composerProject?->hasPackage('phpunit/phpunit') ?? false;

        if (!$hasXml && !$hasPackage) {
            return null;
        }

        $binDir = $composerProject?->getBinDir() ?? 'vendor/bin';

        return new ToolConfig(
            name: $this->name(),
            command: [$binDir . DIRECTORY_SEPARATOR . 'phpunit'],
            args: ['--log-junit', 'var/agentchk/phpunit.junit.xml', '--no-progress']
        );
    }
}
