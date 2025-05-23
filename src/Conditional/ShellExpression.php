<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ShellExpression extends BasicExpression
{
    protected bool $escapedValue = true;

    public static function create(bool $useBashBrackets = true, bool $negateExpression = false): static
    {
        return new self($useBashBrackets, $negateExpression);
    }

    /**
     * @return $this
     */
    public function isOptnameEnabled(ShellInterface|string $optname): self
    {
        $this->operator = ConditionalOperator::SHELL_OPTNAME_ENABLED;
        $this->compareWith = $optname;
        return $this;
    }

    /**
     * @return $this
     */
    public function isVariableSet(ShellInterface|string $variable): self
    {
        $this->operator = ConditionalOperator::SHELL_VARNAME_SET;
        $this->compareWith = $variable;
        return $this;
    }

    /**
     * @return $this
     */
    public function isVariableSetWithNamedReference(ShellInterface|string $variable): self
    {
        $this->operator = ConditionalOperator::SHELL_VARNAME_SET_NAMED_REFERENCE;
        $this->compareWith = $variable;
        return $this;
    }
}
