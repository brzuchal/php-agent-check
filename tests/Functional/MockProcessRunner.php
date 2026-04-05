<?php

namespace Brzuchal\PhpAgentCheck\Tests\Functional;

use Brzuchal\PhpAgentCheck\Service\ProcessRunner;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;

final class MockProcessRunner implements ProcessRunner
{
    public function run(CheckExecution $execution): CheckExecutionResult
    {
        // Return dummy successful result
        return new CheckExecutionResult(0, '{"files":[]}', '');
    }
}
