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
final class Pipeline extends AbstractCollection
{
    public static function pipe(ShellInterface|string $command): self
    {
        $pipeline = new self();
        $pipeline->tuple = $pipeline->toTuple($command, ControlOperator::PIPELINE);
        return $pipeline;
    }

    public static function pipeErrorForward(ShellInterface|string $command): self
    {
        $pipeline = new self();
        $pipeline->tuple = $pipeline->toTuple($command, ControlOperator::PIPELINE_WITH_STDERR_FORWARD);
        return $pipeline;
    }
}
