<?php

namespace Brzuchal\PhpAgentCheck\Tests\Unit;

use Brzuchal\PhpAgentCheck\Application\ComposerProject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ComposerProjectTest extends TestCase
{
    private string $tempDir;
    private Filesystem $fs;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/composer_project_test_' . uniqid();
        $this->fs->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this->tempDir);
    }

    public function testGetBinDirDefault(): void
    {
        $composerProject = new ComposerProject($this->tempDir);
        $this->assertEquals('vendor/bin', $composerProject->getBinDir());
    }

    public function testGetBinDirCustom(): void
    {
        $this->fs->dumpFile($this->tempDir . '/composer.json', json_encode([
            'config' => ['bin-dir' => 'bin']
        ]));
        $composerProject = new ComposerProject($this->tempDir);
        $this->assertEquals('bin', $composerProject->getBinDir());
    }

    public function testHasPackage(): void
    {
        $this->fs->dumpFile($this->tempDir . '/composer.json', json_encode([
            'require' => ['vendor/pkg1' => '*'],
            'require-dev' => ['vendor/pkg2' => '*'],
        ]));
        $composerProject = new ComposerProject($this->tempDir);
        $this->assertTrue($composerProject->hasPackage('vendor/pkg1'));
        $this->assertTrue($composerProject->hasPackage('vendor/pkg2'));
        $this->assertFalse($composerProject->hasPackage('vendor/pkg3'));
    }
}
