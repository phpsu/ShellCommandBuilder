<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class StringExpression extends BasicExpression
{
    protected $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): StringExpression
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @param string|ShellInterface $string
     * @return $this
     */
    public function lenghtZero($string): self
    {
        $this->operator = ConditionalOperator::STRING_LENGHT_ZERO;
        $this->compareWith = $string;
        return $this;
    }

    /**
     * @param string|ShellInterface $string
     * @return $this
     */
    public function lengthNotZero($string): self
    {
        $this->operator = ConditionalOperator::STRING_LENGHT_NOT_ZERO;
        $this->compareWith = $string;
        return $this;
    }

    /**
     * @param string|ShellInterface $stringA
     * @param string|ShellInterface $stringB
     * @return $this
     */
    public function eq($stringA, $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_EQUAL;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @param string|ShellInterface $stringA
     * @param string|ShellInterface $stringB
     * @return $this
     */
    public function equal($stringA, $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_EQUAL_BASH;
        $this->bashEnhancedBrackets = true;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @param string|ShellInterface $stringA
     * @param string|ShellInterface $stringB
     * @return $this
     */
    public function notEqual($stringA, $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_NOT_EQUAL;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @param string|ShellInterface $stringA
     * @param string|ShellInterface $stringB
     * @return $this
     */
    public function sortsBefore($stringA, $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_SORTS_BEFORE;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }

    /**
     * @param string|ShellInterface $stringA
     * @param string|ShellInterface $stringB
     * @return $this
     */
    public function sortsAfter($stringA, $stringB): self
    {
        $this->operator = ConditionalOperator::STRING_SORTS_AFTER;
        $this->compareWith = $stringB;
        $this->compare = $stringA;
        return $this;
    }
}
