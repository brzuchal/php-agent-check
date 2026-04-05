<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpUnit;

use Brzuchal\PhpAgentCheck\Domain\Check;
use Brzuchal\PhpAgentCheck\Domain\CheckContext;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;

final class PhpUnitCheck implements Check
{
    public function __construct(private PhpUnitJunitParser $parser)
    {
    }

    public function name(): string
    {
        return 'phpunit';
    }

    public function supports(CheckContext $context): bool
    {
        $command = $context->config->command[0] ?? 'vendor/bin/phpunit';
        return file_exists($context->workingDirectory . '/' . $command);
    }

    public function createExecution(CheckContext $context): CheckExecution
    {
        $command = $context->config->command ?: ['vendor/bin/phpunit'];
        $args = $context->config->args ?: [];

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
