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
     */
    public static function addOr(ShellInterface|string $command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::OR_OPERATOR);
        return $list;
    }

    /**
     * Returns something like: && echo "hello world"
     */
    public static function addAnd(ShellInterface|string $command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::AND_OPERATOR);
        return $list;
    }

    /**
     * Returns something like: ; echo "hello world"
     */
    public static function add(ShellInterface|string $command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::COMMAND_DELIMITER);
        return $list;
    }

    /**
     * Returns something like: & echo "hello world"
     */
    public static function async(ShellInterface|string $command): self
    {
        $list = new self();
        $list->tuple = $list->toTuple($command, ControlOperator::BASH_AMPERSAND);
        return $list;
    }
}
