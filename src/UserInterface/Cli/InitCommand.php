<?php

namespace Brzuchal\PhpAgentCheck\UserInterface\Cli;

use Brzuchal\PhpAgentCheck\Application\ComposerProject;
use Brzuchal\PhpAgentCheck\Application\ToolDetector;
use Brzuchal\PhpAgentCheck\Domain\ProfileDefinition;
use Brzuchal\PhpAgentCheck\Domain\ProjectConfiguration;
use Brzuchal\PhpAgentCheck\Infrastructure\Config\YamlConfigurationLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InitCommand extends Command
{
    protected static $defaultName = 'init';

    /** @param iterable<ToolDetector> $detectors */
    public function __construct(
        private readonly iterable $detectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialize a new agentchk configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $workingDir = getcwd();
        $configPath = $workingDir . DIRECTORY_SEPARATOR . 'agentchk.yaml';

        if (file_exists($configPath)) {
            $io->error("Configuration file 'agentchk.yaml' already exists.");
            return 1;
        }

        $tools = [];
        $profileTools = [];
        $composerProject = new ComposerProject($workingDir);

        foreach ($this->detectors as $detector) {
            $toolConfig = $detector->detect($workingDir, $composerProject);
            if ($toolConfig !== null) {
                $tools[$detector->name()] = $toolConfig;
                $profileTools[] = $detector->name();
            }
        }

        $config = new ProjectConfiguration(
            profiles: [
                'fast' => new ProfileDefinition('fast', $profileTools),
                'full' => new ProfileDefinition('full', $profileTools),
            ],
            tools: $tools
        );

        $loader = new YamlConfigurationLoader();
        file_put_contents($configPath, $loader->dump($config));
        $io->success("Created 'agentchk.yaml' with detected tools: " . implode(', ', $profileTools));

        return 0;
    }
}
