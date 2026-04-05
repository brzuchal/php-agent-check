<?php

namespace Brzuchal\PhpAgentCheck\Check;

use Brzuchal\PhpAgentCheck\Report\ToolResult;
use Brzuchal\PhpAgentCheck\ProcessRunner;

interface CheckInterface
{
    public function getName(): string;

    /**
     * Executes the check and returns the result.
     *
     * @param ProcessRunner $runner
     * @param array $config The configuration for this tool (command, args, etc.)
     * @return ToolResult
     */
    public function execute(ProcessRunner $runner, array $config): ToolResult;
}
