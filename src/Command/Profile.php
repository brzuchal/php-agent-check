<?php

namespace Brzuchal\PhpAgentCheck\Command;

enum Profile: string
{
    case Fast = 'fast';
    case Full = 'full';
    case Agent = 'agent';
}
