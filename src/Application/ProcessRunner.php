<?php

namespace Brzuchal\PhpAgentCheck\Application;

use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;

interface ProcessRunner
{
    public function run(CheckExecution $execution): CheckExecutionResult;
}
