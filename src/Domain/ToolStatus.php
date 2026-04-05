<?php

namespace Brzuchal\PhpAgentCheck\Domain;

enum ToolStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Error = 'error';
}
