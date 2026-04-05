<?php

namespace Brzuchal\PhpAgentCheck\Domain;

enum ExecutionMode: string
{
    case Human = 'human';
    case Ci = 'ci';
    case Agent = 'agent';
}
