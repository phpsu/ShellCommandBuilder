<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class CollectionTuple implements ShellInterface
{
    /** @var string */
    protected $join = '';

    /** @var string|ShellInterface */
    protected $value = '';

    /**
     * @param string|ShellInterface|mixed $value
     * @param string $join
     * @return static
     * @throws ShellBuilderException
     */
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

    /**
     * @return array<string|ShellInterface|array<mixed>>
     */
    public function __toArray(): array
    {
        $value = $this->value instanceof ShellInterface ? $this->value->__toArray() : $this->value;
        return [$this->join, $value];
    }

    public function __toString(): string
    {
        /** @psalm-suppress ImplicitToStringCast **/
        return sprintf(' %s%s%s', $this->join, $this->value === '' ? '' : ' ', $this->value);
    }
}
