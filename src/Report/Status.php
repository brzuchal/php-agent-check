<?php

namespace Brzuchal\PhpAgentCheck\Report;

enum Status: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Error = 'error';
}
