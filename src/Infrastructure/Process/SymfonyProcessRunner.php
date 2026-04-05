<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Process;

use Brzuchal\PhpAgentCheck\Application\ProcessRunner;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessRunner implements ProcessRunner
{
    public function run(CheckExecution $execution): CheckExecutionResult
    {
        $env = array_merge($execution->environmentVariables, ['AGENTCHK' => '1']);
        
        $process = new Process(
            $execution->command,
            $execution->workingDirectory,
            $env
        );
        $process->setTimeout($execution->timeout);
        $process->run();

        return new CheckExecutionResult(
            exitCode: $process->getExitCode() ?? 255,
            stdout: $process->getOutput(),
            stderr: $process->getErrorOutput()
        );
    }
}
