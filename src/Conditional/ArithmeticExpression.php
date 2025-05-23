<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ArithmeticExpression extends BasicExpression
{
    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): static
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @return $this
     */
    public function equal(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @return $this
     */
    public function notEqual(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_NOT_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @return $this
     */
    public function less(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_LESS_THAN;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @return $this
     */
    public function greater(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_GREATER_THAN;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @return $this
     */
    public function lessEqual(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_LESS_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @return $this
     */
    public function greaterEqual(ShellInterface|string $arg1, ShellInterface|string $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_GREATER_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }
}
