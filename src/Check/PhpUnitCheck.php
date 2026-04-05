<?php

namespace Brzuchal\PhpAgentCheck\Check;

use Brzuchal\PhpAgentCheck\Report\Issue;
use Brzuchal\PhpAgentCheck\Report\ToolResult;
use Brzuchal\PhpAgentCheck\Report\Severity;
use Brzuchal\PhpAgentCheck\Report\Status;
use Brzuchal\PhpAgentCheck\ProcessRunner;

class PhpUnitCheck implements CheckInterface
{
    public function getName(): string
    {
        return 'phpunit';
    }

    public function execute(ProcessRunner $runner, array $config): ToolResult
    {
        $command = array_merge($config['command'] ?? ['vendor/bin/phpunit'], $config['args'] ?? []);
        $process = $runner->run($command, [], 300);

        $result = new ToolResult($this->getName(), $process->isSuccessful() ? Status::Passed : Status::Failed);

        // Very basic output parsing for now if XML is not available yet across versions,
        // although the document requests JUnit parsing. For simplicity let's stick to exit code.

        if (!$process->isSuccessful()) {
            $result->issues[] = new Issue(
                type: 'execution_failure',
                tool: $this->getName(),
                severity: Severity::Error,
                message: "PHPUnit exited with code " . $process->getExitCode(),
                code: $process->getOutput()
            );
        }

        return $result;
    }
}
