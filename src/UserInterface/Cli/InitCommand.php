<?php

namespace Brzuchal\PhpAgentCheck\UserInterface\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

final class InitCommand extends Command
{
    protected static $defaultName = 'init';

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

        if ($this->hasPhpUnit($workingDir)) {
            $tools['phpunit'] = [
                'command' => ['vendor/bin/phpunit'],
                'args' => ['--log-junit', 'var/agentchk/phpunit.junit.xml', '--no-progress'],
            ];
            $profileTools[] = 'phpunit';
        }

        if ($this->hasPhpStan($workingDir)) {
            $tools['phpstan'] = [
                'command' => ['vendor/bin/phpstan'],
                'args' => ['analyse', '--error-format=json'],
            ];
            $profileTools[] = 'phpstan';
        }

        if ($this->hasPhpCs($workingDir)) {
            $tools['phpcs'] = [
                'command' => ['vendor/bin/phpcs'],
                'args' => ['--report=json', 'src', 'tests'],
            ];
            $profileTools[] = 'phpcs';
        }

        $config = [
            'profiles' => [
                'fast' => ['tools' => $profileTools],
                'full' => ['tools' => $profileTools],
            ],
            'tools' => $tools,
        ];

        file_put_contents($configPath, Yaml::dump($config, 4));
        $io->success("Created 'agentchk.yaml' with detected tools: " . implode(', ', $profileTools));

        return 0;
    }

    private function hasPhpUnit(string $dir): bool
    {
        return file_exists($dir . '/phpunit.xml') || file_exists($dir . '/phpunit.xml.dist');
    }

    private function hasPhpStan(string $dir): bool
    {
        return file_exists($dir . '/phpstan.neon') || file_exists($dir . '/phpstan.neon.dist');
    }

    private function hasPhpCs(string $dir): bool
    {
        return file_exists($dir . '/phpcs.xml') || file_exists($dir . '/phpcs.xml.dist');
    }
}
