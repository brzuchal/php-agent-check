<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Config;

use Brzuchal\PhpAgentCheck\Service\ConfigurationLoader;
use Brzuchal\PhpAgentCheck\Domain\ProfileDefinition;
use Brzuchal\PhpAgentCheck\Domain\ProjectConfiguration;
use Brzuchal\PhpAgentCheck\Domain\ToolConfig;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

final readonly class YamlConfigurationLoader implements ConfigurationLoader
{
    public const CONFIGURATION_FILENAMES = [
        'agentchk.yaml',
        'agentchk.yml',
        'agentchk.dist.yaml',
        'agentchk.dist.yml',
    ];

    public function load(?string $workingDirectory = null): ProjectConfiguration
    {
        $dir = $workingDirectory ?? getcwd();
        foreach (self::CONFIGURATION_FILENAMES as $candidate) {
            $path = $dir . DIRECTORY_SEPARATOR . $candidate;
            if (file_exists($path)) {
                $data = Yaml::parseFile($path);
                $processor = new Processor();
                $config = $processor->processConfiguration(new ConfigurationDefinition(), [$data]);

                return $this->mapToProjectConfiguration($config);
            }
        }

        throw new \RuntimeException(
            "No configuration file found. Looked for: " . implode(', ', self::CONFIGURATION_FILENAMES)
        );
    }

    public function dump(ProjectConfiguration $config): string
    {
        return Yaml::dump($config->toArray(), 4);
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
