<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpUnit;

use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpUnitDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpunit';
    }

    public function detect(string $workingDirectory): ?ToolConfig
    {
        $hasXml = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml.dist');

        if (!$hasXml) {
            return null;
        }

        return new ToolConfig(
            name: $this->name(),
            command: ['vendor/bin/phpunit'],
            args: ['--log-junit', 'var/agentchk/phpunit.junit.xml', '--no-progress']
        );
    }
}
