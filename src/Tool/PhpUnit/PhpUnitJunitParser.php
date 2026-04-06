<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpUnit;

use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;
use Brzuchal\PhpAgentCheck\Domain\Issue;
use Brzuchal\PhpAgentCheck\Domain\Severity;
use Brzuchal\PhpAgentCheck\Domain\ToolStatus;

final readonly class PhpUnitJunitParser
{
    public function parse(CheckExecutionResult $result): CheckResult
    {
        // For now, parse via exit code since we do not have JUnit parsing fully done yet
        if ($result->exitCode === 0) {
            return new CheckResult('phpunit', ToolStatus::Passed);
        }

        if ($result->exitCode === 1 || $result->exitCode === 2) {
            return new CheckResult(
                'phpunit',
                ToolStatus::Failed,
                [
                    new Issue(
                        type: 'test_failure',
                        tool: 'phpunit',
                        severity: Severity::Error,
                        message: 'Tests failed. Check output for details.'
                    )
                ]
            );
        }

        return new CheckResult(
            'phpunit',
            ToolStatus::Error,
            [
                new Issue(
                    type: 'execution_error',
                    tool: 'phpunit',
                    severity: Severity::Error,
                    message: "Process returned exit code {$result->exitCode}. " . $result->stderr
                )
            ]
        );
    }
}
