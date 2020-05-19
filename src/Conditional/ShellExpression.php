<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ShellExpression extends BasicExpression
{
    protected $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): ShellExpression
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @param string|ShellInterface $optname
     * @return $this
     */
    public function isOptnameEnabled($optname): self
    {
        $this->operator = ConditionalOperator::SHELL_OPTNAME_ENABLED;
        $this->compareWith = $optname;
        return $this;
    }

    /**
     * @param string|ShellInterface $variable
     * @return $this
     */
    public function isVariableSet($variable): self
    {
        $this->operator = ConditionalOperator::SHELL_VARNAME_SET;
        $this->compareWith = $variable;
        return $this;
    }

    /**
     * @param string|ShellInterface $variable
     * @return $this
     */
    public function isVariableSetWithNamedReference($variable): self
    {
        $this->operator = ConditionalOperator::SHELL_VARNAME_SET_NAMED_REFERENCE;
        $this->compareWith = $variable;
        return $this;
    }
}
