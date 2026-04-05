<?php

namespace Brzuchal\PhpAgentCheck\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Brzuchal\PhpAgentCheck\Configuration;
use Brzuchal\PhpAgentCheck\ProcessRunner;
use Brzuchal\PhpAgentCheck\Check\CheckInterface;
use Brzuchal\PhpAgentCheck\Check\PhpUnitCheck;
use Brzuchal\PhpAgentCheck\Check\PhpStanCheck;
use Brzuchal\PhpAgentCheck\Check\PhpCsCheck;
use Brzuchal\PhpAgentCheck\Report\Report;
use Brzuchal\PhpAgentCheck\Command\ExecutionMode;
use Brzuchal\PhpAgentCheck\Report\Status;

class RunCommand extends Command
{
    private array $checks = [];

    public function __construct()
    {
        parent::__construct();
        $this->checks = [
            'phpunit' => new PhpUnitCheck(),
            'phpstan' => new PhpStanCheck(),
            'phpcs' => new PhpCsCheck(),
        ];
    }

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Run the agent checks')
            ->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Execution mode: human, ci, agent', 'human')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Profile to run', 'fast')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (e.g. json)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = ExecutionMode::from($input->getOption('mode'));
        $profileName = $input->getOption('profile');
        $format = $input->getOption('format');

        $config = new Configuration(getcwd() . '/agentchk.yaml');
        $config->load();

        $profile = $config->getProfile($profileName);
        $tools = $profile['tools'] ?? [];

        $runner = new ProcessRunner(getcwd());
        $report = new Report();
        $hasErrors = false;

        foreach ($tools as $toolName) {
            if (!isset($this->checks[$toolName])) {
                throw new \InvalidArgumentException("Unsupported tool: $toolName");
            }
            /** @var CheckInterface $check */
            $check = $this->checks[$toolName];
            $toolConfig = $config->getTool($toolName);

            $result = $check->execute($runner, $toolConfig);
            $report->tools[] = $result;

            if ($result->status !== Status::Passed) {
                $hasErrors = true;
            }
        }

        $report->status = $hasErrors ? Status::Failed : Status::Passed;

        if ($format === 'json' || $mode === ExecutionMode::Agent) {
            $output->writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $output->writeln("Status: " . $report->status->value);
            foreach ($report->tools as $toolResult) {
                $output->writeln(" - Tool: {$toolResult->name} -> {$toolResult->status->value}");
                foreach ($toolResult->issues as $issue) {
                    $file = $issue->file ?? '';
                    $line = $issue->line ?? '';
                    $output->writeln("   [{$issue->severity->value}] {$issue->message} ({$file}:{$line})");
                }
            }
        }

        return $hasErrors ? Command::FAILURE : Command::SUCCESS;
    }
}
