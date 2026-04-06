<?php

namespace Brzuchal\PhpAgentCheck\Tool\PhpStan;

use Brzuchal\PhpAgentCheck\Domain\Check;
use Brzuchal\PhpAgentCheck\Domain\CheckContext;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Domain\CheckResult;

readonly final class PhpStanCheck implements Check
{
    public function __construct(private PhpStanJsonParser $parser)
    {
    }

    public function name(): string
    {
        return 'phpstan';
    }

    public function supports(CheckContext $context): bool
    {
        $command = $context->config->command[0] ?? 'vendor/bin/phpstan';
        return file_exists($context->workingDirectory . '/' . $command);
    }

    public function createExecution(CheckContext $context): CheckExecution
    {
        $command = $context->config->command ?: ['vendor/bin/phpstan'];
        $args = $context->config->args ?: ['analyse', '--error-format=json'];

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
