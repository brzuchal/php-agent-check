<?php

namespace Brzuchal\PhpAgentCheck\Application;

interface ConfigurationLoader
{
    public function load(?string $workingDirectory = null): array;
}
