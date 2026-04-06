<?php

namespace Brzuchal\PhpAgentCheck\Domain;

final readonly class ProjectConfiguration
{
    /**
     * @param ProfileDefinition[] $profiles
     * @param ToolConfig[] $tools
     */
    public function __construct(
        public array $profiles,
        public array $tools
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

    public function toArray(): array
    {
        $profiles = [];
        foreach ($this->profiles as $name => $profile) {
            $profiles[$name] = $profile->toArray();
        }

        $tools = [];
        foreach ($this->tools as $name => $tool) {
            $tools[$name] = $tool->toArray();
        }

        return [
            'profiles' => $profiles,
            'tools' => $tools,
        ];
    }
}
