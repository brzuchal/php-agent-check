<?php

namespace Brzuchal\PhpAgentCheck\Domain;

interface Check
{
    public function name(): string;

    public function supports(CheckContext $context): bool;

    public function createExecution(CheckContext $context): CheckExecution;

    public function parse(CheckExecutionResult $result): CheckResult;
}
