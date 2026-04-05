<?php

namespace Brzuchal\PhpAgentCheck\Application;

use Brzuchal\PhpAgentCheck\Domain\Check;
use Brzuchal\PhpAgentCheck\Domain\CheckContext;
use Brzuchal\PhpAgentCheck\Domain\Report;

final class AgentCheck
{
    public function __construct(
        private readonly ConfigurationLoader $configLoader,
        private readonly ProcessRunner $processRunner,
        /** @var iterable<Check> */
        private readonly iterable $checks,
        /** @var iterable<ReportWriter> */
        private readonly iterable $reporters
    ) {
    }

    public function run(string $profileName, string $workingDirectory): Report
    {
        $config = $this->configLoader->load($workingDirectory);
        $profile = $config->getProfile($profileName);
        if (!$profile) {
            throw new \RuntimeException("Profile not found: $profileName");
        }

        $toolsToRun = $profile->tools;
        $toolsResults = [];

        /** @var Check $check */
        foreach ($this->checks as $check) {
            if (!in_array($check->name(), $toolsToRun, true)) {
                continue;
            }

            $toolConfig = $config->getToolConfig($check->name());
            $context = new CheckContext($toolConfig, $workingDirectory);

            if (!$check->supports($context)) {
                continue;
            }

            $execution = $check->createExecution($context);
            $result = $this->processRunner->run($execution);
            $checkResult = $check->parse($result);
            $toolsResults[] = $checkResult;
        }

        $report = new Report(
            $this->computeFinalStatus($toolsResults),
            $toolsResults
        );

        foreach ($this->reporters as $reporter) {
            $reporter->write($report);
        }

        return $report;
    }

    /**
     * @param list<\Brzuchal\PhpAgentCheck\Domain\CheckResult> $toolsResults
     */
    private function computeFinalStatus(array $toolsResults): \Brzuchal\PhpAgentCheck\Domain\ToolStatus
    {
        foreach ($toolsResults as $toolResult) {
            if ($toolResult->status !== \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Passed) {
                return \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Failed;
            }
        }

        return \Brzuchal\PhpAgentCheck\Domain\ToolStatus::Passed;
    }
}
