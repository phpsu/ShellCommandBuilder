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

    /**
     * @return array<string|ShellInterface|array<mixed>>
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
