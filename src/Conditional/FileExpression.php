<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class FileExpression extends BasicExpression
{
    protected $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): FileExpression
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function exists($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function existsBlockSpecial($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_BLOCK_SPECIAL;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function existsCharacterSpecial($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_CHARACTER_SPECIAL;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $directory
     * @return $this
     */
    public function isDirectory($directory): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_IS_DIRECTORY;
        $this->compareWith = $directory;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function isRegularFile($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_REGULAR_FILE;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function hasSetGroupIDBit($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXISTS_HAS_SET_GROUP_ID;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function symbolicLink($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_SYMBOLIC_LINK;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function withStickyBit($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_STICKY_BIT;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function isNamedPipe($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_NAMED_PIPE;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function isReadably($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_READABLE;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function notEmpty($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_NOT_EMPTY;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function openReferringToTerminal($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OPEN_REFERING_TO_TERMINAL;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function hasSetUserIDBit($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_HAS_SET_USER_ID;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function writable($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_WRITABLE;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function executable($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_EXECUTABLE;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function ownedByGroupId($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OWNED_BY_GROUP_ID;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function modifiedSinceLastRead($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_MODIFIED_SINCE_LAST_READ;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function ownedByUserId($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_OWNED_BY_USER_ID;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $file
     * @return $this
     */
    public function isSocket($file): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_EXSITS_IS_SOCKET;
        $this->compareWith = $file;
        return $this;
    }

    /**
     * @param string|ShellInterface $fileA
     * @param string|ShellInterface $fileB
     * @return $this
     */
    public function refersToSameDevice($fileA, $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_REFERS_TO_SAME_DEVICE;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }

    /**
     * @param string|ShellInterface $fileA
     * @param string|ShellInterface $fileB
     * @return $this
     */
    public function isNewerThan($fileA, $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_IS_NEWER_THAN;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }

    /**
     * @param string|ShellInterface $fileA
     * @param string|ShellInterface $fileB
     * @return $this
     */
    public function isOlderThan($fileA, $fileB): FileExpression
    {
        $this->operator = ConditionalOperator::FILE_IS_OLDER_THAN;
        $this->compare = $fileA;
        $this->compareWith = $fileB;
        return $this;
    }
}
