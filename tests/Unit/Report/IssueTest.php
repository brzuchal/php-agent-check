<?php

namespace Brzuchal\PhpAgentCheck\Tests\Unit\Report;

use Brzuchal\PhpAgentCheck\Domain\Issue;
use Brzuchal\PhpAgentCheck\Domain\Severity;
use PHPUnit\Framework\TestCase;

class IssueTest extends TestCase
{
    public function testIssueJsonSerialization(): void
    {
        $issue = new Issue(
            type: 'execution_failure',
            tool: 'phpunit',
            severity: Severity::Error,
            message: 'Failed asserting that X is identical to Y'
        );

        $json = json_encode($issue);
        $data = json_decode($json, true);

        $this->assertSame('execution_failure', $data['type']);
        $this->assertSame('phpunit', $data['tool']);
        $this->assertSame('error', $data['severity']);
        $this->assertSame('Failed asserting that X is identical to Y', $data['message']);
    }
}
