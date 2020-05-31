<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\Literal\ShellArgument;
use PHPSu\ShellCommandBuilder\Literal\ShellOption;
use PHPSu\ShellCommandBuilder\Literal\ShellShortOption;
use PHPSu\ShellCommandBuilder\Literal\ShellVariable;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPUnit\Framework\TestCase;

final class ShellWordTest extends TestCase
{
    public function testShellWordAsArgument(): void
    {
        $word = new ShellArgument('hallo');
        $this->assertEquals("'hallo' ", (string)$word);
        $word->setSpaceAfterValue(false);
        $this->assertEquals("'hallo'", (string)$word);
        $word->setEscape(false);
        $this->assertEquals('hallo', (string)$word);
    }

    public function testShellWordAsOptionWithAssign(): void
    {
        $word = new ShellShortOption('e', 'welt');
        $word->setAssignOperator(true);
        $word->setEscape(false);
        $this->assertEquals("-e=welt ", (string)$word);
    }

    public function testShellWordOptionToDebugArray(): void
    {
        $word = new ShellOption('hello', 'world');
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "'world'",
                'argument' => 'hello',
            ],
            $array
        );
    }

    public function testShellVariableToDebugArray(): void
    {
        $word = new ShellVariable('hello', 'world');
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'isVariable' => true,
                'escaped' => true,
                'withAssign' => true,
                'spaceAfterValue' => false,
                'value' => "'world'",
                'argument' => 'hello',
            ],
            $array
        );

        $word = new ShellVariable('hello', ShellBuilder::command('echo'));
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'isVariable' => true,
                'escaped' => false,
                'withAssign' => true,
                'spaceAfterValue' => false,
                'value' => [
                    'executable' => 'echo',
                    'arguments' => [],
                    'isCommandSubstitution' => false,
                    'environmentVariables' => [],
                ],
                'argument' => 'hello',
            ],
            $array
        );
    }

    public function testShellVariableToString(): void
    {
        $word = new ShellVariable('hello', 'world');
        $this->assertEquals('hello=\'world\'', $word->__toString());

        $word = new ShellVariable('hello', ShellBuilder::command('echo'));
        $this->assertEquals('hello=$(echo)', $word->__toString());

        $word = new ShellVariable('hello', ShellBuilder::command('echo'));
        $word->wrapWithBackticks(true);
        $this->assertEquals('hello=`echo`', $word->__toString());
    }

    public function testShellWordShortOptionAsShellInterfaceToDebugArray(): void
    {
        $word = new ShellShortOption('hello', new ShellCommand('hello'));
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => true,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => [
                    'executable' => 'hello',
                    'arguments' => [],
                    'isCommandSubstitution' => false,
                    'environmentVariables' => [],
                ],
                'argument' => 'hello',
            ],
            $array
        );
    }

    public function testShellWordSubCommandAsShellInterfaceToDebugArray(): void
    {
        $word = new ShellArgument((new ShellCommand('hello'))->toggleCommandSubstitution());
        $word->setSpaceAfterValue(false);
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => true,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => false,
                'value' => [
                    'executable' => 'hello',
                    'arguments' => [],
                    'isCommandSubstitution' => true,
                    'environmentVariables' => [],
                ],
                'argument' => '',
            ],
            $array
        );
    }

    public function testArgumentToUppercase(): void
    {
        $word = new ShellOption('hallo');
        $word->setNameUppercase(true);
        $word->setEscape(false);
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => false,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "",
                'argument' => "HALLO",
            ],
            $word->__toArray()
        );
    }

    public function testShellWordAsFaultyValueTypeArgument(): void
    {
        $word = new ShellOption('hallo', 12345);
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Value must be an instance of ShellInterface or a string');
        $word->__toString();
    }

    public function testShellWordWithWrongArgumentType(): void
    {
        $word = new ShellArgument(12345);
        $this->expectExceptionMessage('Value must be an instance of ShellInterface or a string');
        $this->expectException(ShellBuilderException::class);
        $word->__toString();
    }

    public function testShellWordWithEmptyArgument(): void
    {
        $word = new ShellArgument('');
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Argument cant be empty');
        $word->__toString();
    }
}
