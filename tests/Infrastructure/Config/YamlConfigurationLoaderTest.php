<?php

namespace Brzuchal\PhpAgentCheck\Tests\Infrastructure\Config;

use PHPUnit\Framework\TestCase;
use Brzuchal\PhpAgentCheck\Infrastructure\Config\YamlConfigurationLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationLoaderTest extends TestCase
{
    private string $tempDir;
    private Filesystem $fs;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/agentchk_loader_test_' . uniqid();
        $this->fs->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this->tempDir);
    }

    public function testLoadValidConfiguration(): void
    {
        $config = [
            'profiles' => [
                'test' => [
                    'tools' => ['phpunit']
                ]
            ],
            'tools' => [
                'phpunit' => [
                    'command' => ['bin/phpunit'],
                    'args' => ['--no-coverage']
                ]
            ]
        ];
        file_put_contents($this->tempDir . '/agentchk.yaml', Yaml::dump($config));

        $loader = new YamlConfigurationLoader();
        $projectConfig = $loader->load($this->tempDir);

        $this->assertNotNull($projectConfig->getProfile('test'));
        $this->assertEquals(['phpunit'], $projectConfig->getProfile('test')->tools);
        $this->assertEquals(['bin/phpunit'], $projectConfig->getToolConfig('phpunit')->command);
    }

    public function testLoadInvalidConfigurationThrowsException(): void
    {
        $config = [
            'invalid_key' => 'value',
            'profiles' => 'not_an_array'
        ];
        file_put_contents($this->tempDir . '/agentchk.yaml', Yaml::dump($config));

        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidTypeException::class);

        $loader = new YamlConfigurationLoader();
        $loader->load($this->tempDir);
    }

    public function testNoConfigurationFoundThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No configuration file found");

        $loader = new YamlConfigurationLoader();
        $loader->load($this->tempDir);
    }
}
