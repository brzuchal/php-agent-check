<?php

namespace Brzuchal\PhpAgentCheck\Command;

enum ExitCode: int
{
    case Success = 0;
    case ValidationIssues = 1;
    case ExecutionError = 2;
    case InvalidConfig = 3;
}
