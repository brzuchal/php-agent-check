<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpCs;

use Brzuchal\PhpAgentCheck\Domain\Check;
use Brzuchal\PhpAgentCheck\Domain\CheckContext;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;

final class PhpCsCheck implements Check
{
    public function __construct(private PhpCsJsonParser $parser)
    {
    }

    public function name(): string
    {
        return 'phpcs';
    }

    public function supports(CheckContext $context): bool
    {
        $command = $context->config->command[0] ?? 'vendor/bin/phpcs';
        return file_exists($context->workingDirectory . '/' . $command);
    }

    public function createExecution(CheckContext $context): CheckExecution
    {
        $command = $context->config->command ?: ['vendor/bin/phpcs'];
        $args = $context->config->args ?: ['--report=json'];

        return new CheckExecution(
            command: array_merge($command, $args),
            workingDirectory: $context->workingDirectory
        );
    }

    public function parse(CheckExecutionResult $result): CheckResult
    {
        return $this->parser->parse($result);
    }
}
