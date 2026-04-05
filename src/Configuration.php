<?php

namespace Brzuchal\PhpAgentCheck;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    private array $profiles = [];
    private array $tools = [];

    public function __construct(private string $configFile)
    {
    }

    public function load(): void
    {
        if (!file_exists($this->configFile)) {
            throw new \RuntimeException("Configuration file not found: {$this->configFile}");
        }

        $config = Yaml::parseFile($this->configFile);

        $this->profiles = $config['profiles'] ?? [];
        $this->tools = $config['tools'] ?? [];
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
