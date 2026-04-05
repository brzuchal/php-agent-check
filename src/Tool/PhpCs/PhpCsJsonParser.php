<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpCs;

use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;
use Brzuchal\PhpAgentCheck\Domain\Issue;
use Brzuchal\PhpAgentCheck\Domain\Severity;
use Brzuchal\PhpAgentCheck\Domain\ToolStatus;

final class PhpCsJsonParser
{
    public function parse(CheckExecutionResult $result): CheckResult
    {
        if ($result->exitCode === 0 && empty(trim($result->stdout))) {
            return new CheckResult('phpcs', ToolStatus::Passed);
        }

        $data = json_decode($result->stdout, true);

        if (!is_array($data)) {
            return new CheckResult(
                'phpcs',
                ToolStatus::Error,
                [
                    new Issue(
                        type: 'execution_error',
                        tool: 'phpcs',
                        severity: Severity::Error,
                        message: "Failed to parse JSON output. "
                            . "Exit code: {$result->exitCode}\nOutput: " . mb_substr($result->stdout, 0, 100)
                    )
                ]
            );
        }

        $issues = [];
        $files = $data['files'] ?? [];

        foreach ($files as $file => $fileData) {
            $messages = $fileData['messages'] ?? [];
            foreach ($messages as $msg) {
                $severity = ($msg['type'] ?? '') === 'WARNING' ? Severity::Warning : Severity::Error;

                $issues[] = new Issue(
                    type: 'coding_standard',
                    tool: 'phpcs',
                    severity: $severity,
                    message: $msg['message'] ?? 'Unknown error',
                    file: $file,
                    line: $msg['line'] ?? null
                );
            }
        }

        $status = empty($issues) ? ToolStatus::Passed : ToolStatus::Failed;

        return new CheckResult('phpcs', $status, $issues);
    }
}
