<?php

namespace Brzuchal\PhpAgentCheck\Application;

final class ComposerProject
{
    private ?array $composerJson = null;

    public function __construct(
        private readonly string $workingDirectory
    ) {
    }

    public function getBinDir(): string
    {
        $data = $this->getComposerData();
        $binDir = $data['config']['bin-dir'] ?? 'vendor/bin';

        return rtrim($binDir, '/');
    }

    public function hasPackage(string $packageName): bool
    {
        $data = $this->getComposerData();
        $require = $data['require'] ?? [];
        $requireDev = $data['require-dev'] ?? [];

        return isset($require[$packageName]) || isset($requireDev[$packageName]);
    }

    private function getComposerData(): array
    {
        if ($this->composerJson !== null) {
            return $this->composerJson;
        }

        $path = $this->workingDirectory . DIRECTORY_SEPARATOR . 'composer.json';
        if (!file_exists($path)) {
            return $this->composerJson = [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $this->composerJson = [];
        }

        try {
            return $this->composerJson = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->composerJson = [];
        }
    }
}
