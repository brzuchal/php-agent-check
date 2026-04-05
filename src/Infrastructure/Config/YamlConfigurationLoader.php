<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Config;

use Brzuchal\PhpAgentCheck\Application\ConfigurationLoader;
use Brzuchal\PhpAgentCheck\Domain\ProfileDefinition;
use Brzuchal\PhpAgentCheck\Domain\ProjectConfiguration;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;
use Symfony\Component\Yaml\Yaml;

final class YamlConfigurationLoader implements ConfigurationLoader
{
    public function load(?string $workingDirectory = null): ProjectConfiguration
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
                $data = Yaml::parseFile($path);

                return $this->mapToProjectConfiguration($data);
            }
        }

        throw new \RuntimeException("No configuration file found. Looked for: " . implode(', ', $candidates));
    }

    private function mapToProjectConfiguration(array $data): ProjectConfiguration
    {
        $profiles = [];
        foreach ($data['profiles'] ?? [] as $name => $profileData) {
            $profiles[$name] = new ProfileDefinition(
                $name,
                $profileData['tools'] ?? []
            );
        }

        $tools = [];
        foreach ($data['tools'] ?? [] as $name => $toolData) {
            $tools[$name] = new ToolConfig(
                $name,
                $toolData['command'] ?? [],
                $toolData['args'] ?? []
            );
        }

        return new ProjectConfiguration($profiles, $tools);
    }
}
