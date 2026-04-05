<?php

namespace Brzuchal\PhpAgentCheck\Infrastructure\Reporter;

use Brzuchal\PhpAgentCheck\Domain\Severity;
use Brzuchal\PhpAgentCheck\Service\ReportWriter;
use Brzuchal\PhpAgentCheck\Domain\Report;
use Symfony\Component\Console\Output\OutputInterface;

final class HumanReportWriter implements ReportWriter
{
    public function __construct(private readonly OutputInterface $output)
    {
    }

    public function write(Report $report): void
    {
        $statusStyle = $report->status->value === 'passed' ? 'info' : 'error';
        $this->output->writeln("<{$statusStyle}>Status: " . strtoupper($report->status->value) . "</{$statusStyle}>");
        foreach ($report->tools as $toolResult) {
            $toolStatusStyle = $toolResult->status->value === 'passed' ? 'info' : 'error';
            $this->output->writeln(sprintf(
                ' - Tool: <comment>%s</comment> -> <%s>%s</%s>',
                $toolResult->tool,
                $toolStatusStyle,
                $toolResult->status->value,
                $toolStatusStyle
            ));
            foreach ($toolResult->issues as $issue) {
                $file = $issue->file ?? '';
                $line = $issue->line ?? '';
                $severityStyle = match ($issue->severity) {
                    Severity::Error => 'error',
                    Severity::Warning => 'comment',
                };
                $this->output->writeln(sprintf(
                    '   [<%s>%s</%s>] %s (<href=file://%s>%s:%s</>)',
                    $severityStyle,
                    $issue->severity->value,
                    $severityStyle,
                    $issue->message,
                    $file,
                    $file,
                    $line
                ));
            }
        }
    }
}
