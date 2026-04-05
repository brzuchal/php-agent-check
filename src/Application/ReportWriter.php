<?php

namespace Brzuchal\PhpAgentCheck\Application;

use Brzuchal\PhpAgentCheck\Domain\Report;

interface ReportWriter
{
    public function write(Report $report): void;
}
