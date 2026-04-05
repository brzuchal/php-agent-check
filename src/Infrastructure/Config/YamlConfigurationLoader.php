<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Config;

use Brzuchal\PhpAgentCheck\Application\ConfigurationLoader;
use Symfony\Component\Yaml\Yaml;

final class YamlConfigurationLoader implements ConfigurationLoader
{
    public function load(?string $workingDirectory = null): array
    {
        $candidates = [
            'agentchk.yaml',
            'agentchk.yml',
            'agentchk.dist.yaml',
            'agentchk.dist.yml',
        ];

        $dir = $workingDirectory ?? getcwd();
        foreach ($candidates as $candidate) {
            $path = $dir . DIRECTORY_SEPARATOR . $candidate;
            if (file_exists($path)) {
                return Yaml::parseFile($path);
            }
        }

        throw new \RuntimeException("No configuration file found. Looked for: " . implode(', ', $candidates));
    }
}
