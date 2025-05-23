<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class CollectionTuple implements ShellInterface
{
    private string $join = '';

    private ShellInterface|string $value = '';

    private bool $noSpaceBeforeJoin = false;

    private bool $noSpaceAfterJoin = false;

    public static function create(ShellInterface|string $value, string $join = ''): self
    {
        $tuple = new self();
        $tuple->value = $value;
        $tuple->join = $join;
        return $tuple;
    }

    public function noSpaceBeforeJoin(bool $space): self
    {
        $this->noSpaceBeforeJoin = $space;
        return $this;
    }

    public function noSpaceAfterJoin(bool $space): self
    {
        $this->noSpaceAfterJoin = $space;
        return $this;
    }

    /**
     * @return array<ShellInterface|string|array<mixed>>
     */
    public function __toArray(): array
    {
        $value = $this->value instanceof ShellInterface ? $this->value->__toArray() : $this->value;
        return [$this->join, $value];
    }

    public function __toString(): string
    {
        /** @psalm-suppress ImplicitToStringCast **/
        return sprintf('%s%s%s%s', $this->noSpaceBeforeJoin ? '' : ' ', $this->join, ($this->value === '' || $this->noSpaceAfterJoin) ? '' : ' ', $this->value);
    }
}
