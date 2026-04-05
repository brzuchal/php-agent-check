<?php

namespace Brzuchal\PhpAgentCheck\Tests\Functional;

use Brzuchal\PhpAgentCheck\Tool\PhpCs\PhpCsDetector;
use Brzuchal\PhpAgentCheck\Tool\PhpStan\PhpStanDetector;
use Brzuchal\PhpAgentCheck\Tool\PhpUnit\PhpUnitDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Brzuchal\PhpAgentCheck\Command\InitCommand;
use Symfony\Component\Filesystem\Filesystem;

class InitCommandTest extends TestCase
{
    private string $tempDir;
    private Filesystem $fs;
    private array $detectors;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/agentchk_test_' . uniqid();
        $this->fs->mkdir($this->tempDir);
        $this->detectors = [
            new PhpUnitDetector(),
            new PhpStanDetector(),
            new PhpCsDetector(),
        ];
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this->tempDir);
    }

    public function testInitDetectsToolsFromComposerJson(): void
    {
        $composerJson = [
            'config' => ['bin-dir' => 'custom-bin'],
            'require-dev' => [
                'phpunit/phpunit' => '^10.0',
                'phpstan/phpstan' => '^1.0',
            ],
        ];
        $this->fs->dumpFile($this->tempDir . '/composer.json', json_encode($composerJson));

        $command = new InitCommand($this->detectors);
        $tester = new CommandTester($command);

        $oldCwd = getcwd();
        chdir($this->tempDir);

        try {
            $tester->execute([]);

            $this->assertEquals(0, $tester->getStatusCode());
            $this->assertStringContainsString(
                "Created 'agentchk.yaml' with detected tools: phpunit, phpstan",
                $tester->getDisplay()
            );
            $this->assertFileExists($this->tempDir . '/agentchk.yaml');

            $config = file_get_contents($this->tempDir . '/agentchk.yaml');
            $this->assertStringContainsString('custom-bin/phpunit', $config);
            $this->assertStringContainsString('custom-bin/phpstan', $config);
            $this->assertStringNotContainsString('phpcs', $config);
        } finally {
            chdir($oldCwd);
        }
    }

    public function testInitDetectsTools(): void
    {
        // Fixture: create config files for tools
        $this->fs->touch($this->tempDir . '/phpunit.xml');
        $this->fs->touch($this->tempDir . '/phpstan.neon');
        $this->fs->touch($this->tempDir . '/phpcs.xml');

        $command = new InitCommand($this->detectors);
        $tester = new CommandTester($command);

        // We need to change CWD because InitCommand uses getcwd()
        $oldCwd = getcwd();
        chdir($this->tempDir);

        try {
            $tester->execute([]);

            $this->assertEquals(0, $tester->getStatusCode());
            $this->assertStringContainsString(
                "Created 'agentchk.yaml' with detected tools: phpunit, phpstan, phpcs",
                $tester->getDisplay()
            );
            $this->assertFileExists($this->tempDir . '/agentchk.yaml');

            $config = file_get_contents($this->tempDir . '/agentchk.yaml');
            $this->assertStringContainsString('phpunit:', $config);
            $this->assertStringContainsString('phpstan:', $config);
            $this->assertStringContainsString('phpcs:', $config);
        } finally {
            chdir($oldCwd);
        }
    }

    public function testInitFailsIfConfigExists(): void
    {
        $this->fs->touch($this->tempDir . '/agentchk.yaml');

        $command = new InitCommand($this->detectors);
        $tester = new CommandTester($command);

        $oldCwd = getcwd();
        chdir($this->tempDir);

        try {
            $tester->execute([]);
            $this->assertEquals(1, $tester->getStatusCode());
            $this->assertStringContainsString(
                "Configuration file 'agentchk.yaml' already exists.",
                $tester->getDisplay()
            );
        } finally {
            chdir($oldCwd);
        }
    }
}
