<?php

namespace Brzuchal\PhpAgentCheck;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessRunner
{
    public function __construct(private string $workingDirectory)
    {
    }

    public function run(array $command, array $env = [], float|int|null $timeout = 60): Process
    {
        $env = array_merge($env, ['AGENTCHK' => '1']);
        $process = new Process($command, $this->workingDirectory, $env);
        $process->setTimeout($timeout);
        $process->run();

        return $process;
    }
}
