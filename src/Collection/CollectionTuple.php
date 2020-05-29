<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class CollectionTuple implements ShellInterface
{
    /** @var string */
    protected $join = '';

    /** @var string|ShellInterface */
    protected $value = '';
    /** @var bool */
    private $noSpaceBeforeJoin = false;
    /** @var bool */
    private $noSpaceAfterJoin = false;

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
        return sprintf('%s%s%s%s', $this->noSpaceBeforeJoin ? '' : ' ', $this->join, ($this->value === '' || $this->noSpaceAfterJoin) ? '' : ' ', $this->value);
    }
}
