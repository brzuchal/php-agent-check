<?php

namespace Brzuchal\PhpAgentCheck\Command;

enum ExecutionMode: string
{
    case Human = 'human';
    case Ci = 'ci';
    case Agent = 'agent';
}
