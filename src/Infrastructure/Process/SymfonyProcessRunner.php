<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Process;

use Brzuchal\PhpAgentCheck\Application\ProcessRunner;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyProcessRunner implements ProcessRunner
{
    public function __construct(private readonly ?OutputInterface $output = null)
    {
    }

    public function run(CheckExecution $execution): CheckExecutionResult
    {
        $env = array_merge($execution->environmentVariables, ['AGENTCHK' => '1']);

        $process = new Process(
            $execution->command,
            $execution->workingDirectory,
            $env
        );
        $process->setTimeout($execution->timeout);

        if ($this->output && $this->output->isVerbose()) {
            $this->output->writeln('<info>[ProcessRunner] Executing:</info> ' . $process->getCommandLine());
            $this->output->writeln('<info>[ProcessRunner] Working Directory:</info> ' . $execution->workingDirectory);
        }

        $process->run();

        if ($this->output && $this->output->isVerbose()) {
            $this->output->writeln('<info>[ProcessRunner] Exit Code:</info> ' . ($process->getExitCode() ?? 'null'));
            if ($process->getExitCode() !== 0) {
                $this->output->writeln('<error>[ProcessRunner] Stderr:</error> ' . $process->getErrorOutput());
            }
        }

        return new CheckExecutionResult(
            exitCode: $process->getExitCode() ?? 255,
            stdout: $process->getOutput(),
            stderr: $process->getErrorOutput()
        );
    }
}
