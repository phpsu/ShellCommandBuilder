<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class StringExpression extends BasicExpression
{
    protected bool $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): static
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @return $this
     */
    public function lenghtZero(ShellInterface|string $string): self
    {
        $this->operator = ConditionalOperator::STRING_LENGHT_ZERO;
        $this->compareWith = $string;
        return $this;
    }

    /**
     * @return $this
     */
    public function lengthNotZero(ShellInterface|string $string): self
    {
        $this->operator = ConditionalOperator::STRING_LENGHT_NOT_ZERO;
        $this->compareWith = $string;
        return $this;
    }

    /**
     * @return $this
     */
    public function eq(ShellInterface|string $stringA, ShellInterface|string $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_EQUAL;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @return $this
     */
    public function equal(ShellInterface|string $stringA, ShellInterface|string $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_EQUAL_BASH;
        $this->bashEnhancedBrackets = true;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @return $this
     */
    public function notEqual(ShellInterface|string $stringA, ShellInterface|string $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_NOT_EQUAL;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @return $this
     */
    public function sortsBefore(ShellInterface|string $stringA, ShellInterface|string $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_SORTS_BEFORE;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @return $this
     */
    public function sortsAfter(ShellInterface|string $stringA, ShellInterface|string $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_SORTS_AFTER;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }
}
