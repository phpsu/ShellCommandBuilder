<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

abstract class AbstractCollection implements CollectionInterface
{
    /** @var CollectionTuple|null */
    protected $tuple;

    /**
     * @param string|ShellInterface $command
     * @param string $join
     * @return CollectionTuple
     * @throws ShellBuilderException
     */
    protected function toTuple($command, string $join): CollectionTuple
    {
        return CollectionTuple::create($command, $join);
    }

    public function __toString(): string
    {
        return (string)$this->tuple;
    }
}
