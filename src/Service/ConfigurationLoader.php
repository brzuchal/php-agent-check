<?php

namespace Brzuchal\PhpAgentCheck\Service;

use Brzuchal\PhpAgentCheck\Domain\ProjectConfiguration;

interface ConfigurationLoader
{
    public function load(?string $workingDirectory = null): ProjectConfiguration;
}
