<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpCs;

use Brzuchal\PhpAgentCheck\Application\ComposerProject;
use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpCsDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpcs';
    }

    public function detect(string $workingDirectory, ?ComposerProject $composerProject = null): ?ToolConfig
    {
        $hasXml = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpcs.xml')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpcs.xml.dist');
        $hasPackage = $composerProject?->hasPackage('squizlabs/php_codesniffer') ?? false;

        if (!$hasXml && !$hasPackage) {
            return null;
        }

        $binDir = $composerProject?->getBinDir() ?? 'vendor/bin';

        return new ToolConfig(
            name: $this->name(),
            command: [$binDir . DIRECTORY_SEPARATOR . 'phpcs'],
            args: ['--report=json', 'src', 'tests']
        );
    }
}
