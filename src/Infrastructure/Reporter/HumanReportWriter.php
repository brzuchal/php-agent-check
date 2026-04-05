<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Reporter;

use Brzuchal\PhpAgentCheck\Application\ReportWriter;
use Brzuchal\PhpAgentCheck\Domain\Report;
use Symfony\Component\Console\Output\OutputInterface;

final class HumanReportWriter implements ReportWriter
{
    public function __construct(private OutputInterface $output)
    {
    }

    public function write(Report $report): void
    {
        $this->output->writeln("Status: " . $report->status->value);
        foreach ($report->tools as $toolResult) {
            $this->output->writeln(" - Tool: {$toolResult->tool} -> {$toolResult->status->value}");
            foreach ($toolResult->issues as $issue) {
                $file = $issue->file ?? '';
                $line = $issue->line ?? '';
                $this->output->writeln("   [{$issue->severity->value}] {$issue->message} ({$file}:{$line})");
            }
        }
    }
}
