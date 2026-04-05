<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpStan;

use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;
use Brzuchal\PhpAgentCheck\Domain\Issue;
use Brzuchal\PhpAgentCheck\Domain\Severity;
use Brzuchal\PhpAgentCheck\Domain\ToolStatus;

final class PhpStanJsonParser
{
    public function parse(CheckExecutionResult $result): CheckResult
    {
        if ($result->exitCode === 0 && empty(trim($result->stdout))) {
            return new CheckResult('phpstan', ToolStatus::Passed);
        }

        $data = json_decode($result->stdout, true);

        if (!is_array($data)) {
            return new CheckResult(
                'phpstan',
                ToolStatus::Error,
                [
                    new Issue(
                        type: 'execution_error',
                        tool: 'phpstan',
                        severity: Severity::Error,
                        message: "Failed to parse JSON output. "
                            . "Exit code: {$result->exitCode}\nOutput: {$result->stdout}"
                    )
                ]
            );
        }

        $issues = [];
        $files = $data['files'] ?? [];

        foreach ($files as $file => $fileData) {
            $messages = $fileData['messages'] ?? [];
            foreach ($messages as $msg) {
                $issues[] = new Issue(
                    type: 'static_analysis',
                    tool: 'phpstan',
                    severity: Severity::Error,
                    message: $msg['message'] ?? 'Unknown error',
                    file: $file,
                    line: $msg['line'] ?? null,
                    code: $msg['identifier'] ?? null
                );
            }
        }

        $status = empty($issues) ? ToolStatus::Passed : ToolStatus::Failed;

        return new CheckResult('phpstan', $status, $issues);
    }
}
