<?php

namespace Brzuchal\PhpAgentCheck\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Brzuchal\PhpAgentCheck\AgentCheckApplication;

class AgentCheckApplicationTest extends TestCase
{
    public function testRunCommandInHumanMode(): void
    {
        $application = new AgentCheckApplication('1.0.0');
        $command = $application->find('run');
        
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--profile' => 'fast',
            '--mode' => 'human',
        ]);
        
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        
        $this->assertStringContainsString('Status: passed', $output);
        $this->assertStringContainsString('Tool: phpunit -> passed', $output);
    }
    
    public function testRunCommandInAgentModeOutputsJson(): void
    {
        $application = new AgentCheckApplication('1.0.0');
        $command = $application->find('run');
        
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--profile' => 'agent',
            '--mode' => 'agent',
        ]);
        
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('tools', $data);
        $this->assertEquals('passed', $data['status']);
    }
}
