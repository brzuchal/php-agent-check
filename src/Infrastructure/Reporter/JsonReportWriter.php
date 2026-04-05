<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Reporter;

use Brzuchal\PhpAgentCheck\Service\ReportWriter;
use Brzuchal\PhpAgentCheck\Domain\Report;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonReportWriter implements ReportWriter
{
    public function __construct(private readonly OutputInterface $output)
    {
    }

    public function write(Report $report): void
    {
        $this->output->writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
