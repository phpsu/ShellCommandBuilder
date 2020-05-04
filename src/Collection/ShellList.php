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
    public function addOr($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::OR_OPERATOR);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function addAnd($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::AND_OPERATOR);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function add($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::COMMAND_DELIMITER);
        return $this;
    }
}
