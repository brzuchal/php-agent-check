<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final class ProjectConfiguration
{
    /**
     * @param ProfileDefinition[] $profiles
     * @param ToolConfig[] $tools
     */
    public function __construct(
        public readonly array $profiles,
        public readonly array $tools
    ) {
    }

    public function getProfile(string $name): ?ProfileDefinition
    {
        return $this->profiles[$name] ?? null;
    }

    public function getToolConfig(string $name): ToolConfig
    {
        return $this->tools[$name] ?? new ToolConfig($name);
    }
}
