<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Conditional;

use PHPSu\ShellCommandBuilder\Conditional\ArithmeticExpression;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;

final class ArithmeticExpressionTest extends TestCase
{
    public function testEqualCondition(): void
    {
        $condition = ArithmeticExpression::create()->equal('a', 'b');
        $this->assertEquals('[[ a -eq b ]]', (string)$condition);
    }

    public function testEqualConditionForNonBashShell(): void
    {
        $condition = ArithmeticExpression::create(false)->equal('a', 'b');
        $this->assertEquals('[ a -eq b ]', (string)$condition);
    }

    public function testEqualConditionNegated(): void
    {
        $condition = ArithmeticExpression::create(true, true)->equal('a', 'b');
        $this->assertEquals('[[ ! a -eq b ]]', (string)$condition);
    }

    public function testNotEqualCondition(): void
    {
        $condition = ArithmeticExpression::create()->notEqual('a', 'b');
        $this->assertEquals('[[ a -ne b ]]', (string)$condition);
    }

    public function testLessCondition(): void
    {
        $condition = ArithmeticExpression::create()->less('a', 'b');
        $this->assertEquals('[[ a -lt b ]]', (string)$condition);
    }

    public function testGreaterCondition(): void
    {
        $condition = ArithmeticExpression::create()->greater('a', 'b');
        $this->assertEquals('[[ a -gt b ]]', (string)$condition);
    }

    public function testLessEqualCondition(): void
    {
        $condition = ArithmeticExpression::create()->lessEqual('a', 'b');
        $this->assertEquals('[[ a -le b ]]', (string)$condition);
    }

    public function testGreaterEqualCondition(): void
    {
        $condition = ArithmeticExpression::create()->greaterEqual('a', 'b');
        $this->assertEquals('[[ a -ge b ]]', (string)$condition);
    }

    public function testGreaterEqualWithCommandComparison(): void
    {
        $command = ShellBuilder::new()
            ->createCommand('date')
            ->addArgument('+%H', false)
            ->toggleCommandSubstitution();
        $condition = ArithmeticExpression::create()->greaterEqual($command, '16');
        $this->assertEquals('[[ $(date +%H) -ge 16 ]]', (string)$condition);
    }
}
