<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpCs;

use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;

final class PhpCsDetector implements ToolDetector
{
    public function name(): string
    {
        return 'phpcs';
    }

    public function detect(string $workingDirectory): ?ToolConfig
    {
        $hasXml = file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpcs.xml')
            || file_exists($workingDirectory . DIRECTORY_SEPARATOR . 'phpcs.xml.dist');

        if (!$hasXml) {
            return null;
        }

        return new ToolConfig(
            name: $this->name(),
            command: ['vendor/bin/phpcs'],
            args: ['--report=json', 'src', 'tests']
        );
    }
}
