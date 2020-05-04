<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class CollectionTuple implements ShellInterface
{
    /** @var string */
    protected $join;

    /** @var string|ShellInterface */
    protected $value;

    public static function create($value, string $join = ''): self
    {
        if (!(is_string($value) || $value instanceof ShellInterface)) {
            throw new ShellBuilderException('Value must be of Type string or an instance of ShellInterface');
        }
        $tuple = new self();
        $tuple->value = $value;
        $tuple->join = $join;
        return $tuple;
    }

    public function __toArray(): array
    {
        $value = $this->value instanceof ShellInterface ? $this->value->__toArray() : $this->value;
        return [$this->join, $value];
    }

    public function __toString(): string
    {
        return sprintf(' %s %s', $this->join, $this->value);
    }
}
