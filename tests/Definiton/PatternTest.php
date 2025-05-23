<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Definiton;

use PHPSu\ShellCommandBuilder\Definition\Pattern;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPUnit\Framework\TestCase;

final class PatternTest extends TestCase
{
    public function testSplitNothingSpecial(): void
    {
        $this->assertEquals(['a', 'b', 'c', 'd'], Pattern::split('a b c d'));
    }

    public function testSplitQuotedStrings(): void
    {
        $this->assertEquals(['a', 'b b', 'a'], Pattern::split('a "b b" a'));
    }

    public function testSplitSingleQuotedStrings(): void
    {
        $this->assertEquals(["a", "'b' c", "d"], Pattern::split('a "\'b\' c" d'));
    }

    public function testSplitFileName(): void
    {
        $this->assertEquals(['/home/user/dev/hallo welt.txt'], Pattern::split('/home/user/dev/hallo\ welt.txt'));
    }

    public function testSplitDoubleQuotedStrings(): void
    {
        $this->assertEquals(["a", '"b" c', "d"], Pattern::split('a "\"b\" c" d'));
    }

    public function testSplitEscapedSpaceStrings(): void
    {
        $this->assertEquals(["a", "b c", "d"], Pattern::split('a b\ c d'));
    }

    public function testBadDoubleQuotes(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('The given input has mismatching Quotes');
        Pattern::split('a "b c d e');
    }

    public function testBadSingleQuotes(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('The given input has mismatching Quotes');
        Pattern::split("a 'b c d e");
    }

    public function testBadMultipleQuotes(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('The given input has mismatching Quotes');
        Pattern::split("one '\"\"\"");
    }

    public function testSplitTrailingWhitespace(): void
    {
        $this->assertEquals(["a", "b", "c", "d"], Pattern::split('a b c d '));
    }

    public function testMultibyte(): void
    {
        // currently multibyte is not escaped - make sure escaping happens before
        $this->assertEquals(["あい", "あい"], Pattern::split('あい あい'));
    }

    public function testSplitPercentSign(): void
    {
        $this->assertEquals(["abc", "%foo bar%"], Pattern::split("abc '%foo bar%'"));
    }
}
