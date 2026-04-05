<?php

namespace Brzuchal\PhpAgentCheck;

use Symfony\Component\Console\Application;

class AgentCheckApplication extends Application
{
    public function __construct(string $version = '1.0.0')
    {
        parent::__construct('agentchk', $version);
        $this->add(new Command\RunCommand());
        $this->setDefaultCommand('run', true);
    }
}
