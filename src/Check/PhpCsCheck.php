<?php

namespace Brzuchal\PhpAgentCheck\Check;

use Brzuchal\PhpAgentCheck\Report\Issue;
use Brzuchal\PhpAgentCheck\Report\ToolResult;
use Brzuchal\PhpAgentCheck\Report\Severity;
use Brzuchal\PhpAgentCheck\Report\Status;
use Brzuchal\PhpAgentCheck\ProcessRunner;

class PhpCsCheck implements CheckInterface
{
    public function getName(): string
    {
        return 'phpcs';
    }

    public function execute(ProcessRunner $runner, array $config): ToolResult
    {
        $command = array_merge($config['command'] ?? ['vendor/bin/phpcs'], $config['args'] ?? []);
        // Ignore exit code 1 or 2 as they indicate found issues, allow 300s timeout
        $process = $runner->run($command, [], 300);

        $exitCode = $process->getExitCode();
        // 0 = no errors, 1 = errors found, 2 = warnings found, 3 = processing error
        $status = ($exitCode === 0) ? Status::Passed : Status::Failed;
        $result = new ToolResult($this->getName(), $status);

        // PHPCS returns > 0 when issues are found. It can return 1, 2, or even 3 depending on fixable errors.
        if ($exitCode > 0) {
            $output = $process->getOutput();
            $data   = json_decode($output, true);
            if (is_array($data) && isset($data['files'])) {
                foreach ($data['files'] as $file => $fileData) {
                    foreach ($fileData['messages'] ?? [] as $message) {
                        $result->issues[] = new Issue(
                            type: 'phpcs_' . strtolower($message['type']),
                            tool: $this->getName(),
                            severity: strtolower($message['type']) === 'error' ? Severity::Error : Severity::Warning,
                            message: $message['message'],
                            file: $file,
                            line: $message['line'] ?? null,
                            code: $message['source'] ?? null
                        );
                    }
                }
            } else {
                $result->issues[] = new Issue(
                    type: 'execution_failure',
                    tool: $this->getName(),
                    severity: Severity::Error,
                    message: "PHPCS execution failed (exit code: $exitCode)",
                    code: $output ?: $process->getErrorOutput()
                );
            }
        }

        return $result;
    }
}
