<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
abstract class AbstractCollection implements ShellInterface
{
    protected CollectionTuple|null $tuple = null;

    protected function toTuple(ShellInterface|string $command, string $join): CollectionTuple
    {
        return CollectionTuple::create($command, $join);
    }

    /**
     * @return array<ShellInterface|string|array<mixed>>
     * @throws ShellBuilderException
     */
    public function __toArray(): array
    {
        if ($this->tuple === null) {
            throw new ShellBuilderException('Tuple has not been set yet - collection cannot be parsed to array');
        }

        return $this->tuple->__toArray();
    }

    public function __toString(): string
    {
        return (string)$this->tuple;
    }
}
