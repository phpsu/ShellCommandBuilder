<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellList extends AbstractCollection
{
    /**
     * Returns something like: || echo "hello world"
     *
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public static function addOr($command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::OR_OPERATOR);
        return $list;
    }

    /**
     * Returns something like: && echo "hello world"
     *
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public static function addAnd($command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::AND_OPERATOR);
        return $list;
    }

    /**
     * Returns something like: ; echo "hello world"
     *
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public static function add($command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::COMMAND_DELIMITER);
        return $list;
    }

    /**
     * Returns something like: & echo "hello world"
     *
     * @param string|ShellInterface $command
     * @return static
     * @throws ShellBuilderException
     */
    public static function async($command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::BASH_AMPERSAND);
        return $list;
    }
}
