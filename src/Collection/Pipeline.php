<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\ShellCommand;

final class Pipeline extends AbstractCollection
{
    public function pipe($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::PIPELINE);
        return $this;
    }

    public function pipeErrorForward($command): self
    {
        $this->tuple = $this->toTuple($command, ControlOperator::PIPELINE_WITH_STDERR_FORWARD);
        return $this;
    }
}
