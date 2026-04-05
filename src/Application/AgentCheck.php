<?php

namespace Brzuchal\PhpAgentCheck\Application;

use Brzuchal\PhpAgentCheck\Domain\Check;
use Brzuchal\PhpAgentCheck\Domain\CheckContext;
use Brzuchal\PhpAgentCheck\Domain\Report;

final class AgentCheck
{
    public function __construct(
        private ConfigurationLoader $configLoader,
        private ProcessRunner $processRunner,
        /** @var iterable<Check> */
        private iterable $checks,
        /** @var iterable<ReportWriter> */
        private iterable $reporters
    ) {
    }

    public function run(string $profileName, string $workingDirectory): Report
    {
        $config = $this->configLoader->load($workingDirectory);
        $profileConfig = $config['profiles'][$profileName] ?? null;
        if (!$profileConfig) {
            throw new \RuntimeException("Profile not found: $profileName");
        }

        $toolsToRun = $profileConfig['tools'] ?? [];
        $report = new Report();

        /** @var Check $check */
        foreach ($this->checks as $check) {
            if (!in_array($check->name(), $toolsToRun, true)) {
                continue;
            }

            $toolConfig = $config['tools'][$check->name()] ?? [];
            $context = new CheckContext($toolConfig, $workingDirectory);

            if (!$check->supports($context)) {
                continue;
            }

            $execution = $check->createExecution($context);
            $result = $this->processRunner->run($execution);
            $checkResult = $check->parse($result);
            $report->tools[] = $checkResult;
        }

        $this->computeFinalStatus($report);

        foreach ($this->reporters as $reporter) {
            $reporter->write($report);
        }

        return $report;
    }

    private function computeFinalStatus(Report $report): void
    {
        $hasErrors = false;
        foreach ($report->tools as $toolResult) {
            if ($toolResult->status !== \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Passed) {
                $hasErrors = true;
            }
        }
        $report->status = $hasErrors ?
            \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Failed :
            \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Passed;
    }
}
