<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;

final class ShellList extends AbstractCollection
{
    public function addOr($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::OR_OPERATOR);
        return $this;
    }

    public function addAnd($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::AND_OPERATOR);
        return $this;
    }

    public function add($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::COMMAND_DELIMITER);
        return $this;
    }
}
