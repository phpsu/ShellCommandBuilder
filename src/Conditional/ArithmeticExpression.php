<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ArithmeticExpression extends BasicExpression
{
    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): ArithmeticExpression
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function equal($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function notEqual($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_NOT_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function less($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_LESS_THAN;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function greater($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_GREATER_THAN;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function lessEqual($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_LESS_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }

    /**
     * @param string|ShellInterface $arg1
     * @param string|ShellInterface $arg2
     * @return $this
     */
    public function greaterEqual($arg1, $arg2): self
    {
        $this->operator = ConditionalOperator::ARTITH_GREATER_EQUAL;
        $this->compare = $arg1;
        $this->compareWith = $arg2;
        return $this;
    }
}
