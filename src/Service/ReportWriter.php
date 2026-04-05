<?php

namespace Brzuchal\PhpAgentCheck\Service;

use Brzuchal\PhpAgentCheck\Domain\Report;

interface ReportWriter
{
    public function write(Report $report): void;
}
