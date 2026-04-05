<?php

namespace Brzuchal\PhpAgentCheck\Domain;

enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
}
