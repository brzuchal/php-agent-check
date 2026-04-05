<?php

namespace Brzuchal\PhpAgentCheck\Report;

enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
}
