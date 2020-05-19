<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Conditional;

use PHPSu\ShellCommandBuilder\Conditional\StringExpression;
use PHPUnit\Framework\TestCase;

final class StringExpressionTest extends TestCase
{
    public function testStringLenghtZero(): void
    {
        $condition = StringExpression::create(false)->lenghtZero('string');
        $this->assertEquals('[ -z "string" ]', (string)$condition);
    }

    public function testStringLengthNotZero(): void
    {
        $condition = StringExpression::create(false)->lengthNotZero('string');
        $this->assertEquals('[ -n "string" ]', (string)$condition);
    }

    public function testStringEqualsSimpleBrackets(): void
    {
        $condition = StringExpression::create(false)->eq('stringA', 'stringB');
        $this->assertEquals('[ "stringA" = "stringB" ]', (string)$condition);
    }

    public function testStringEqualBashBrackets(): void
    {
        $condition = StringExpression::create()->equal('stringA', 'stringB');
        $this->assertEquals('[[ "stringA" == "stringB" ]]', (string)$condition);
    }

    public function testStringNotEqual(): void
    {
        $condition = StringExpression::create(false)->notEqual('stringA', 'stringB');
        $this->assertEquals('[ "stringA" != "stringB" ]', (string)$condition);
    }

    public function testStringSortsBefore(): void
    {
        $condition = StringExpression::create(false)->sortsBefore('stringA', 'stringB');
        $this->assertEquals('[ "stringA" < "stringB" ]', (string)$condition);
    }

    public function testStringSortsAfter(): void
    {
        $condition = StringExpression::create(false)->sortsAfter('stringA', 'stringB');
        $this->assertEquals('[ "stringA" > "stringB" ]', (string)$condition);
    }
}
