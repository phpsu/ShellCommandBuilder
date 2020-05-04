<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class Pipeline extends AbstractCollection
{
    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipe($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::PIPELINE);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipeErrorForward($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::PIPELINE_WITH_STDERR_FORWARD);
        return $this;
    }
}
