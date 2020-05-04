<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
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

    public function testShellCommandWithCommandSubstitution(): void
    {
        $command = new ShellCommand('ls');
        $command->addShortOption('ld')
            ->addArgument(
                (new ShellCommand('date'))
                    ->addArgument('+%B', false)
                ->toggleCommandSubstitution()
            )
            ->addNoSpaceArgument('.txt')
        ;
        $this->assertEquals("ls -ld $(date +%B).txt", (string)$command);
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
            ['prefix' => '--', 'argument' =>  "color", 'suffix' =>  '=', 'value' =>  '\'true\'']
        ], $command['arguments']);
    }

    public function testShortOptionWithWrongType(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        (new ShellCommand('ls'))->addShortOption('la', false);
    }

    public function testOptionWithWrongType(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        (new ShellCommand('ls'))->addOption('la', 124343);
    }

    public function testArgumentWithWrongType(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        (new ShellCommand('ls'))->addArgument(new \DateTime());
    }

    public function testNoSpaceArgumentWithWrongType(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        (new ShellCommand('ls'))->addNoSpaceArgument(new GroupType());
    }
}
