<?php

namespace Brzuchal\PhpAgentCheck\UserInterface\Cli;

use Brzuchal\PhpAgentCheck\Application\AgentCheck;
use Brzuchal\PhpAgentCheck\Domain\ExecutionMode;
use Brzuchal\PhpAgentCheck\Domain\Profile;
use Brzuchal\PhpAgentCheck\Domain\ToolStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class AgentCheckCommand extends Command
{
    public function __construct(private AgentCheck $agentCheck)
    {
        parent::__construct('run');
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
            $modeOpt = $input->getOption('mode');
            $envMode = getenv('AGENTCHK_MODE');
            if ($envMode !== false) {
                $modeOpt = $envMode === '1' ? 'agent' : $envMode;
            } elseif (getenv('AGENT_MODE') === '1') {
                $modeOpt = 'agent';
            }
            $mode = ExecutionMode::tryFrom($modeOpt) ?? ExecutionMode::Human;

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
