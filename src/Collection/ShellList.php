<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ShellList extends AbstractCollection
{
    /**
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
}
