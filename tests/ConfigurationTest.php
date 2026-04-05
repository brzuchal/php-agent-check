<?php

namespace Brzuchal\PhpAgentCheck\Tests;

use PHPUnit\Framework\TestCase;
use Brzuchal\PhpAgentCheck\Configuration;

class ConfigurationTest extends TestCase
{
    public function testLoadsConfiguration(): void
    {
        $config = new Configuration(__DIR__ . '/../agentchk.yaml');
        $config->load();

        $profile = $config->getProfile('fast');
        $this->assertContains('phpunit', $profile['tools']);

        $tool = $config->getTool('phpunit');
        $this->assertEquals(['vendor/bin/phpunit'], $tool['command']);
    }
}
