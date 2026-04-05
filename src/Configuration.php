<?php

namespace Brzuchal\PhpAgentCheck;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    private array $profiles = [];
    private array $tools = [];

    public function __construct(private string|null $configFile = null, private string|null $workingDirectory = null)
    {
    }

    public function load(): void
    {
        if ($this->configFile === null) {
            $this->configFile = $this->findConfigFile();
        } elseif (!file_exists($this->configFile)) {
            throw new \RuntimeException("Configuration file not found: {$this->configFile}");
        }

        $config = Yaml::parseFile($this->configFile);

        $this->profiles = $config['profiles'] ?? [];
        $this->tools = $config['tools'] ?? [];
    }

    private function findConfigFile(): string
    {
        $candidates = [
            'agentchk.yaml',
            'agentchk.yml',
            'agentchk.dist.yaml',
            'agentchk.dist.yml',
        ];

        $dir = $this->workingDirectory ?? getcwd();
        foreach ($candidates as $candidate) {
            $path = $dir . DIRECTORY_SEPARATOR . $candidate;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("No configuration file found. Looked for: " . implode(', ', $candidates));
    }

    public function getProfile(string $name): array
    {
        if (!isset($this->profiles[$name])) {
            throw new \InvalidArgumentException("Profile not found: $name");
        }

        return $this->profiles[$name];
    }

    public function getTool(string $name): array
    {
        if (!isset($this->tools[$name])) {
            throw new \InvalidArgumentException("Tool configuration not found: $name");
        }

        return $this->tools[$name];
    }
}
