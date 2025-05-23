<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class FileExpression extends BasicExpression
{
    protected bool $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): static
    {
        return new self($useBashBrackets, $negateExpression);
    }

    public function exists(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS;
        $this->compareWith = $file;
        return $this;
    }

    public function existsBlockSpecial(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_BLOCK_SPECIAL;
        $this->compareWith = $file;
        return $this;
    }

    public function existsCharacterSpecial(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_CHARACTER_SPECIAL;
        $this->compareWith = $file;
        return $this;
    }

    public function isDirectory(ShellInterface|string $directory): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_IS_DIRECTORY;
        $this->compareWith = $directory;
        return $this;
    }

    public function isRegularFile(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_REGULAR_FILE;
        $this->compareWith = $file;
        return $this;
    }

    public function hasSetGroupIDBit(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_HAS_SET_GROUP_ID;
        $this->compareWith = $file;
        return $this;
    }

    public function symbolicLink(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_SYMBOLIC_LINK;
        $this->compareWith = $file;
        return $this;
    }

    public function withStickyBit(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_STICKY_BIT;
        $this->compareWith = $file;
        return $this;
    }

    public function isNamedPipe(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_NAMED_PIPE;
        $this->compareWith = $file;
        return $this;
    }

    public function isReadably(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_READABLE;
        $this->compareWith = $file;
        return $this;
    }

    public function notEmpty(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_NOT_EMPTY;
        $this->compareWith = $file;
        return $this;
    }

    public function openReferringToTerminal(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OPEN_REFERING_TO_TERMINAL;
        $this->compareWith = $file;
        return $this;
    }

    public function hasSetUserIDBit(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_HAS_SET_USER_ID;
        $this->compareWith = $file;
        return $this;
    }

    public function writable(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_WRITABLE;
        $this->compareWith = $file;
        return $this;
    }

    public function executable(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_EXECUTABLE;
        $this->compareWith = $file;
        return $this;
    }

    public function ownedByGroupId(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OWNED_BY_GROUP_ID;
        $this->compareWith = $file;
        return $this;
    }

    public function modifiedSinceLastRead(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_MODIFIED_SINCE_LAST_READ;
        $this->compareWith = $file;
        return $this;
    }

    public function ownedByUserId(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OWNED_BY_USER_ID;
        $this->compareWith = $file;
        return $this;
    }

    public function isSocket(ShellInterface|string $file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_IS_SOCKET;
        $this->compareWith = $file;
        return $this;
    }

    public function refersToSameDevice(ShellInterface|string $fileA, ShellInterface|string $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_REFERS_TO_SAME_DEVICE;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }

    public function isNewerThan(ShellInterface|string $fileA, ShellInterface|string $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_IS_NEWER_THAN;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }

    public function isOlderThan(ShellInterface|string $fileA, ShellInterface|string $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_IS_OLDER_THAN;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }
}
