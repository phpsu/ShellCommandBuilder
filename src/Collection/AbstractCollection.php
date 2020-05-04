<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

abstract class AbstractCollection implements CollectionInterface
{
    /** @var CollectionTuple */
    protected $tuple;

    public function toTuple($command, string $join): CollectionTuple
    {
        return CollectionTuple::create($command, $join);
    }

    public function __toString(): string
    {
        return (string)$this->tuple;
    }
}
