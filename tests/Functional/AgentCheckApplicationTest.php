<?php

namespace Brzuchal\PhpAgentCheck\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Brzuchal\PhpAgentCheck\UserInterface\Cli\AgentCheckCommand;
use Brzuchal\PhpAgentCheck\Application\AgentCheck;
use Brzuchal\PhpAgentCheck\Infrastructure\Config\YamlConfigurationLoader;
use Brzuchal\PhpAgentCheck\Application\ProcessRunner;
use Brzuchal\PhpAgentCheck\Domain\CheckExecution;
use Brzuchal\PhpAgentCheck\Domain\CheckExecutionResult;
use Brzuchal\PhpAgentCheck\Infrastructure\Reporter\HumanReportWriter;
use Brzuchal\PhpAgentCheck\Tool\PhpUnit\PhpUnitCheck;
use Brzuchal\PhpAgentCheck\Tool\PhpUnit\PhpUnitJunitParser;
use Brzuchal\PhpAgentCheck\Tool\PhpStan\PhpStanCheck;
use Brzuchal\PhpAgentCheck\Tool\PhpStan\PhpStanJsonParser;
use Brzuchal\PhpAgentCheck\Tool\PhpCs\PhpCsCheck;
use Brzuchal\PhpAgentCheck\Tool\PhpCs\PhpCsJsonParser;
use Symfony\Component\Console\Output\BufferedOutput;

class AgentCheckApplicationTest extends TestCase
{
    public function testRunCommandInHumanMode(): void
    {
        $output = new BufferedOutput();
        $reporters = [new HumanReportWriter($output)];
        $checks = [
            new PhpUnitCheck(new PhpUnitJunitParser()),
            new PhpStanCheck(new PhpStanJsonParser()),
            new PhpCsCheck(new PhpCsJsonParser()),
        ];

        $agentCheck = new AgentCheck(
            new YamlConfigurationLoader(),
            new MockProcessRunner(),
            $checks,
            $reporters
        );

        $command = new AgentCheckCommand($agentCheck);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--profile' => 'fast',
            '--mode' => 'human',
        ]);

        $display = $output->fetch();
        $this->assertStringContainsString('Status:', $display);
        $this->assertStringContainsString('Tool: phpunit ->', $display);
    }
}
