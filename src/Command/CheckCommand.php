<?php

namespace Brzuchal\PhpAgentCheck\Command;

use Brzuchal\PhpAgentCheck\Service\AgentCheck;
use Brzuchal\PhpAgentCheck\Domain\ExecutionMode;
use Brzuchal\PhpAgentCheck\Domain\Profile;
use Brzuchal\PhpAgentCheck\Domain\ToolStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckCommand extends Command
{
    public function __construct(private readonly AgentCheck $agentCheck)
    {
        parent::__construct('check');
    }

    protected function configure(): void
    {
        $this->setDescription('Run the agent checks')
            ->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Execution mode: human, ci, agent', 'human')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Profile to run', 'fast')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (e.g. json)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $profile = Profile::from($input->getOption('profile'));
        } catch (\Throwable $e) {
            $output->writeln("<error>Invalid Configuration: " . $e->getMessage() . "</error>");
            return 3; // Invalid Configuration
        }

        try {
            $report = $this->agentCheck->run($profile->value, getcwd());

            return $report->status === ToolStatus::Passed ? 0 : 1;
        } catch (\Throwable $e) {
            $output->writeln("<error>Execution Error: " . $e->getMessage() . "</error>");
            return 2;
        }
    }
}
