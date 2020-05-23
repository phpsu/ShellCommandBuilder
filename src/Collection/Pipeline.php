<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class Pipeline extends AbstractCollection
{
    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public static function pipe($command): self
    {
        $pipeline = new self();
        $pipeline->tuple = $pipeline->toTuple($command, ControlOperator::PIPELINE);
        return $pipeline;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public static function pipeErrorForward($command): self
    {
        $pipeline = new self();
        $pipeline->tuple = $pipeline->toTuple($command, ControlOperator::PIPELINE_WITH_STDERR_FORWARD);
        return $pipeline;
    }
}
