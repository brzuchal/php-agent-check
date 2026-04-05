<?php

namespace Brzuchal\PhpAgentCheck\Application;

use Brzuchal\PhpAgentCheck\Domain\ProjectConfiguration;

interface ConfigurationLoader
{
    public function load(?string $workingDirectory = null): ProjectConfiguration;
}
