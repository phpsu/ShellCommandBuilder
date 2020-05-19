<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Conditional;

use PHPSu\ShellCommandBuilder\Conditional\ShellExpression;
use PHPUnit\Framework\TestCase;

final class ShellExpressionTest extends TestCase
{
    public function testOptnameEnabled(): void
    {
        $condition = ShellExpression::create()->isOptnameEnabled('bgnice');
        $this->assertEquals('[[ -o "bgnice" ]]', (string)$condition);
    }

    public function testVariableSet(): void
    {
        $condition = ShellExpression::create()->isVariableSet('var');
        $this->assertEquals('[[ -v "var" ]]', (string)$condition);
    }

    public function testVariableSetNamedReference(): void
    {
        $condition = ShellExpression::create()->isVariableSetWithNamedReference('refVar');
        $this->assertEquals('[[ -R "refVar" ]]', (string)$condition);
    }
}
