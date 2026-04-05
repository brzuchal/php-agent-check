<?php

namespace Brzuchal\PhpAgentCheck\Domain;

enum Profile: string
{
    case Fast = 'fast';
    case Full = 'full';
    case Agent = 'agent';
}
