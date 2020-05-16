<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellWord;
use PHPUnit\Framework\TestCase;

final class ShellWordTest extends TestCase
{
    public function testShellWordAsArgument(): void
    {
        $word = new ShellWord('hallo');
        $word->asArgument();
        $this->assertEquals("'hallo' ", (string)$word);
        $word->setSpaceAfterValue(false);
        $this->assertEquals("'hallo'", (string)$word);
        $word->setEscape(false);
        $this->assertEquals('hallo', (string)$word);
    }

    public function testShellWordAsOptionWithAssign(): void
    {
        $word = new ShellWord('e', 'welt');
        $word->asShortOption();
        $word->setAssignOperator(true);
        $word->setEscape(false);
        $this->assertEquals("-e=welt ", (string)$word);
    }

    public function testShellWordOptionToDebugArray(): void
    {
        $word = new ShellWord('hello', 'world');
        $word->asOption();
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "'world'",
                'argument' => 'hello',
            ],
            $array
        );
    }

    public function testShellWordShortOptionAsShellInterfaceToDebugArray(): void
    {
        $word = new ShellWord('hello', new ShellCommand('hello'));
        $word->asShortOption();
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => true,
                'isOption' => false,
                'isEnvironmentVariable' => false,
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
        $word = new ShellWord((new ShellCommand('hello'))->toggleCommandSubstitution());
        $word->setSpaceAfterValue(false);
        $word->asArgument();
        $array = $word->__toArray();
        $this->assertEquals(
            [
                'isArgument' => true,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => false,
                'value' => '',
                'argument' => [
                    'executable' => 'hello',
                    'arguments' => [],
                    'isCommandSubstitution' => true,
                    'environmentVariables' => [],
                ],
            ],
            $array
        );
    }

    public function testResetFlagOnSettingItTwiceForSubcommand(): void
    {
        $word = new ShellWord('hallo');
        $word->asArgument();
        $word->asOption();
        $word->asShortOption();
        $word->setEscape(true);
        $word->asArgument();
        $this->assertEquals(
            [
                'isArgument' => true,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "",
                'argument' => "'hallo'",
            ],
            $word->__toArray()
        );
    }

    public function testResetFlagOnSettingItTwiceForOption(): void
    {
        $word = new ShellWord('hallo');
        $word->asArgument();
        $word->asOption();
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "",
                'argument' => "hallo",
            ],
            $word->__toArray()
        );
    }

    public function testResetFlagOnSettingItTwiceForShortOption(): void
    {
        $word = new ShellWord('hallo', 'value');
        $word->asArgument();
        $word->asShortOption();
        $word->setEscape(false);
        $this->assertEquals(
            [
                'isArgument' => false,
                'isShortOption' => true,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'escaped' => false,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "value",
                'argument' => "hallo",
            ],
            $word->__toArray()
        );
    }

    public function testResetFlagOnSettingItTwiceForArgumentWithError(): void
    {
        $word = new ShellWord('hallo', 'value');
        $word->asShortOption();
        $word->asArgument();
        $word->setEscape(false);
        $this->expectExceptionMessage('An argument cant have a value');
        $this->expectException(ShellBuilderException::class);
        $word->__toArray();
    }

    public function testResetFlagOnSettingItTwiceForArgument(): void
    {
        $word = new ShellWord('hallo');
        $word->asOption();
        $word->asArgument();
        $this->assertEquals(
            [
                'isArgument' => true,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'escaped' => true,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "",
                'argument' => "'hallo'",
            ],
            $word->__toArray()
        );
    }

    public function testShellWordAsFaultyArgument(): void
    {
        $word = new ShellWord('hallo', 'value');
        $word->asArgument();
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('An argument cant have a value');
        $word->__toString();
    }

    public function testShellWordAsFaultyValueForArgument(): void
    {
        $word = new ShellWord('hallo', new ShellCommand('hi'));
        $word->asArgument();
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('An argument cant have a value');
        $word->__toString();
    }

    public function testShellWordAsFaultyValueTypeArgument(): void
    {
        $word = new ShellWord('hallo', 12345);
        $word->asOption();
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Value must be an instance of ShellInterface or a string');
        $word->__toString();
    }

    public function testShellWordWithWrongArgumentType(): void
    {
        $word = new ShellWord(12345);
        $word->asArgument();
        $this->expectExceptionMessage('Argument must be an instance of ShellInterface or a string');
        $this->expectException(ShellBuilderException::class);
        $word->__toString();
    }

    public function testShellWordWithNoShellWordType(): void
    {
        $word = new ShellWord('test');
        $this->expectExceptionMessage('No ShellWord-Type defined - use e.g. asArgument() to define it');
        $this->expectException(ShellBuilderException::class);
        $word->__toString();
    }

    public function testShellWordWithWrongValueType(): void
    {
        $word = new ShellWord('test', 12345);
        $word->asOption();
        $this->expectExceptionMessage('Value must be an instance of ShellInterface or a string');
        $this->expectException(ShellBuilderException::class);
        $word->__toString();
    }

    public function testShellWordWithEmptyArgument(): void
    {
        $word = new ShellWord('');
        $word->asArgument();
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Argument cant be empty');
        $word->__toString();
    }
}
