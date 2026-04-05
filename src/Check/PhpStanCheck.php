<?php

namespace Brzuchal\PhpAgentCheck\Check;

use Brzuchal\PhpAgentCheck\Report\Issue;
use Brzuchal\PhpAgentCheck\Report\ToolResult;
use Brzuchal\PhpAgentCheck\ProcessRunner;
use Brzuchal\PhpAgentCheck\Report\Severity;
use Brzuchal\PhpAgentCheck\Report\Status;

class PhpStanCheck implements CheckInterface
{
    public function getName(): string
    {
        return 'phpstan';
    }

    public function execute(ProcessRunner $runner, array $config): ToolResult
    {
        $command = array_merge($config['command'] ?? ['vendor/bin/phpstan'], $config['args'] ?? []);
        $process = $runner->run($command, [], 300);

        $result = new ToolResult($this->getName(), $process->isSuccessful() ? Status::Passed : Status::Failed);

        if (!$process->isSuccessful()) {
            $output = $process->getOutput();
            $data = json_decode($output, true);
            if (is_array($data) && isset($data['files'])) {
                foreach ($data['files'] as $file => $fileData) {
                    foreach ($fileData['messages'] ?? [] as $message) {
                        $result->issues[] = new Issue(
                            type: 'phpstan_error',
                            tool: $this->getName(),
                            severity: Severity::Error,
                            message: $message['message'],
                            file: $file,
                            line: $message['line'] ?? null
                        );
                    }
                }
            } else {
                $result->issues[] = new Issue(
                    type: 'execution_failure',
                    tool: $this->getName(),
                    severity: Severity::Error,
                    message: "PHPStan failed",
                    code: $output ?: $process->getErrorOutput()
                );
            }
        }

        return $result;
    }
}
