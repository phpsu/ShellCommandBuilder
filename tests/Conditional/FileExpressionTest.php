<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Conditional;

use PHPSu\ShellCommandBuilder\Conditional\FileExpression;
use PHPUnit\Framework\TestCase;

final class FileExpressionTest extends TestCase
{
    public function testFileExists(): void
    {
        $condition = FileExpression::create()->exists('file');
        $this->assertEquals('[[ -a "file" ]]', (string)$condition);
    }

    public function testFileExistsBlockSpecial(): void
    {
        $condition = FileExpression::create()->existsBlockSpecial('file');
        $this->assertEquals('[[ -b "file" ]]', (string)$condition);
    }

    public function testFileExistsCharacterSpecial(): void
    {
        $condition = FileExpression::create()->existsCharacterSpecial('file');
        $this->assertEquals('[[ -c "file" ]]', (string)$condition);
    }

    public function testFileIsDirectory(): void
    {
        $condition = FileExpression::create()->isDirectory('file');
        $this->assertEquals('[[ -d "file" ]]', (string)$condition);
    }

    public function testFileIsRegularFile(): void
    {
        $condition = FileExpression::create()->isRegularFile('file');
        $this->assertEquals('[[ -f "file" ]]', (string)$condition);
    }

    public function testFileHasSetGroupIDBit(): void
    {
        $condition = FileExpression::create()->hasSetGroupIDBit('file');
        $this->assertEquals('[[ -g "file" ]]', (string)$condition);
    }

    public function testFileSymbolicLink(): void
    {
        $condition = FileExpression::create()->symbolicLink('file');
        $this->assertEquals('[[ -h "file" ]]', (string)$condition);
    }

    public function testFileWithStickyBit(): void
    {
        $condition = FileExpression::create()->withStickyBit('file');
        $this->assertEquals('[[ -k "file" ]]', (string)$condition);
    }

    public function testFileIsNamedPipe(): void
    {
        $condition = FileExpression::create()->isNamedPipe('file');
        $this->assertEquals('[[ -p "file" ]]', (string)$condition);
    }

    public function testFileIsReadably(): void
    {
        $condition = FileExpression::create()->isReadably('file');
        $this->assertEquals('[[ -r "file" ]]', (string)$condition);
    }

    public function testFileNotEmpty(): void
    {
        $condition = FileExpression::create()->notEmpty('file');
        $this->assertEquals('[[ -s "file" ]]', (string)$condition);
    }

    public function testFileOpenReferringToTerminal(): void
    {
        $condition = FileExpression::create()->openReferringToTerminal('file');
        $this->assertEquals('[[ -t "file" ]]', (string)$condition);
    }

    public function testFileHasSetUserIDBit(): void
    {
        $condition = FileExpression::create()->hasSetUserIDBit('file');
        $this->assertEquals('[[ -u "file" ]]', (string)$condition);
    }

    public function testFileWritable(): void
    {
        $condition = FileExpression::create()->writable('file');
        $this->assertEquals('[[ -w "file" ]]', (string)$condition);
    }

    public function testFileExecutable(): void
    {
        $condition = FileExpression::create()->executable('file');
        $this->assertEquals('[[ -x "file" ]]', (string)$condition);
    }

    public function testFileOwnedByGroupId(): void
    {
        $condition = FileExpression::create()->ownedByGroupId('file');
        $this->assertEquals('[[ -G "file" ]]', (string)$condition);
    }

    public function testFileModifiedSinceLastRead(): void
    {
        $condition = FileExpression::create()->modifiedSinceLastRead('file');
        $this->assertEquals('[[ -N "file" ]]', (string)$condition);
    }

    public function testFileOwnedByUserId(): void
    {
        $condition = FileExpression::create()->ownedByUserId('file');
        $this->assertEquals('[[ -O "file" ]]', (string)$condition);
    }

    public function testFileIsSocket(): void
    {
        $condition = FileExpression::create()->isSocket('file');
        $this->assertEquals('[[ -S "file" ]]', (string)$condition);
    }

    public function testFileRefersToSameDevice(): void
    {
        $condition = FileExpression::create()->refersToSameDevice('fileA', 'fileB');
        $this->assertEquals('[[ "fileA" -ef "fileB" ]]', (string)$condition);
    }

    public function testFileIsNewerThan(): void
    {
        $condition = FileExpression::create()->isNewerThan('fileA', 'fileB');
        $this->assertEquals('[[ "fileA" -nt "fileB" ]]', (string)$condition);
    }

    public function testFileIsOlderThan(): void
    {
        $condition = FileExpression::create()->isOlderThan('fileA', 'fileB');
        $this->assertEquals('[[ "fileA" -ot "fileB" ]]', (string)$condition);
    }
}
