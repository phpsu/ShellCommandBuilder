<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

use DateTime;
use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPUnit\Framework\TestCase;

class ShellCommandTest extends TestCase
{
    public function testShellCommand(): void
    {
        $command = new ShellCommand('mysql');
        $command->addShortOption('u', 'username')
            ->addShortOption('p', 'password')
            ->addShortOption('h', '127.0.0.1')
            ->addArgument('database')
            ->addOption('skip-comments')
        ;
        $this->assertEquals("mysql -u 'username' -p 'password' -h '127.0.0.1' 'database' --skip-comments", (string)$command);
    }

    public function testShellCommandWithEnvironmentVariables(): void
    {
        $command = new ShellCommand('grep');
        $command->addEnv('grep_color', '1;35')
            ->addOption('color', 'always')
            ->addArgument('root')
            ->addArgument('/etc/passwd', false);
        $this->assertEquals("GREP_COLOR='1;35' grep --color 'always' 'root' /etc/passwd", (string)$command);
    }

    public function testShellCommandWithCommandSubstitution(): void
    {
        $command = new ShellCommand('ls');
        $command->addShortOption('ld')
            ->addNoSpaceArgument(
                (new ShellCommand('date'))
                    ->addArgument('+%B', false)
                ->toggleCommandSubstitution()
            )
            ->addArgument('.txt', false)
        ;
        $this->assertEquals("ls -ld $(date +%B).txt", (string)$command);
    }

    public function testShellCommandWithProcessSubstitution(): void
    {
        $command = new ShellCommand('diff');
        $command->addArgument((new ShellCommand('date'))
                    ->addArgument('+%B', false)
                    ->isProcessSubstitution(), false);
        $this->assertEquals("diff <(date +%B)", (string)$command);
    }

    public function testSwitchSubstitutionType(): void
    {
        $subCommand = (new ShellCommand('date'))
            ->addArgument('+%B', false)
            ->isProcessSubstitution();
        $command = new ShellCommand('diff');
        $command->addArgument($subCommand, false);
        $this->assertEquals("diff <(date +%B)", (string)$command);
        $subCommand->toggleCommandSubstitution();
        $this->assertEquals("diff date +%B", (string)$command);
        $subCommand->isProcessSubstitution(false);
        $this->assertEquals("diff $(date +%B)", (string)$command);
    }

    public function testShellCommandWithInvertedOutput(): void
    {
        $command = new ShellCommand('echo');
        $command->invert()->addShortOption('e', 'hello world');
        $this->assertEquals("! echo -e 'hello world'", (string)$command);
    }

    public function testEscapeOptionWithAssignOperator(): void
    {
        $command = (string)(new ShellCommand('ls'))->addOption('color', 'true', true, true);
        $this->assertEquals("ls --color='true'", $command);
    }

    public function testShellCommandToArray(): void
    {
        $command = (new ShellCommand('ls'))->addOption('color', 'true', true, true)->__toArray();
        $this->assertEquals('ls', $command['executable']);
        $this->assertEquals([
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => true,
                'spaceAfterValue' => true,
                'value' => "'true'",
                'argument' =>  "color",
            ]
        ], $command['arguments']);
    }

    public function testShellCommandArgumentToArray(): void
    {
        $command = (new ShellCommand('ls'))->addArgument('test', false)->__toArray();
        $this->assertEquals('ls', $command['executable']);
        $this->assertEquals([
            [
                'isArgument' => true,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => false,
                'withAssign' => false,
                'spaceAfterValue' => true,
                'value' => "test",
                'argument' => '',
            ]
        ], $command['arguments']);
    }

    public function testAccessBuilderBeforeCreatingIt(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('You need to create a ShellBuilder first before you can use it within a command');
        (new ShellCommand('hi'))->addToBuilder();
    }

    public function testShellCommandWithCommandSubstitutionToArray(): void
    {
        $shell = (new ShellCommand('ls'))
            ->addOption('color', 'true', true, true)
            ->addEnv('a', 'b')
        ;
        $shell->toggleCommandSubstitution();
        $this->assertEquals('$(A=\'b\' ls --color=\'true\')', (string)$shell);
        $command = $shell->__toArray();
        $this->assertEquals('ls', $command['executable']);
        $this->assertEquals(true, $command['isCommandSubstitution']);
        $this->assertEquals([
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => false,
                'isEnvironmentVariable' => true,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => true,
                'spaceAfterValue' => true,
                'value' => "'b'",
                'argument' => "A",
            ]
        ], $command['environmentVariables']);
        $this->assertEquals([
            [
                'isArgument' => false,
                'isShortOption' => false,
                'isOption' => true,
                'isEnvironmentVariable' => false,
                'isVariable' => false,
                'escaped' => true,
                'withAssign' => true,
                'spaceAfterValue' => true,
                'value' => "'true'",
                'argument' =>  "color",
            ]
        ], $command['arguments']);
    }

    public function testConditionalArguments(): void
    {
        $command = ShellBuilder::command('test')
            ->if(false, static fn(ShellCommand $command): ShellCommand => $command->addOption('f', 'false'))
            ->if(true, static fn(ShellCommand $command): ShellCommand => $command->addOption('t', 'true'));
        static::assertEquals((string)$command, "test --t 'true'");
    }

    public function testUnEscapedOption(): void
    {
        $command = (new ShellCommand('ls'))->addOption('color', 'true', false, true);
        $this->assertEquals('ls --color=true', (string)$command);
    }

    public function testUnEscapedNoAssignOperatorOption(): void
    {
        $command = (new ShellCommand('ls'))->addOption('color', 'true', false, false);
        $this->assertEquals('ls --color true', (string)$command);
    }
}
