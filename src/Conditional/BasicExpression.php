<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Conditional;

use PHPSu\ShellCommandBuilder\Definition\ConditionalOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
abstract class BasicExpression implements ShellInterface
{
    /**
     * This is not POSIX-compatible (only eg. Korn and Bash), beware before using it
     * @var bool
     */
    protected $bashEnhancedBrackets = false;
    /** @var bool this is always double quoted */
    protected $escapedValue = false;
    /** @var bool */
    private $negateExpression;
    /** @var string|ShellInterface */
    protected $compare = '';
    /** @var string|ShellInterface */
    protected $compareWith = '';
    /** @var string */
    protected $operator = '';

    public function __construct(bool $useBashBrackets, bool $negateExpression)
    {
        $this->negateExpression = $negateExpression;
        $this->bashEnhancedBrackets = $useBashBrackets;
    }

    public function escapeValue(bool $enable): BasicExpression
    {
        $this->escapedValue = $enable;
        return $this;
    }

    /**
     * @todo with min. support of php 7.4 this can be fully implemented here
     * @param bool $useBashBrackets
     * @param bool $negateExpression
     * @return mixed
     */
    abstract public static function create(bool $useBashBrackets = false, bool $negateExpression = false);


    /**
     * @param string|ShellInterface $value
     * @return string|array<mixed>
     */
    private function getValueDebug($value)
    {
        if ($value instanceof ShellInterface) {
            return $value->__toArray();
        }
        return $value;
    }

    /**
     * @param string|ShellInterface $value
     * @return string
     */
    private function getValue($value): string
    {
        $return = $value;
        if ($value instanceof ShellInterface) {
            $return = $value->__toString();
        }
        if ($this->escapedValue) {
            /** @psalm-suppress ImplicitToStringCast */
            $return = sprintf('"%s"', $return);
        }
        return $return;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s%s%s%s %s',
            $this->bashEnhancedBrackets ? ConditionalOperator::BRACKET_LEFT_BASH : ConditionalOperator::BRACKET_LEFT,
            $this->negateExpression ? '! ' : '',
            $this->compare ? $this->getValue($this->compare) . ' ' : '',
            $this->operator,
            $this->compareWith ? ' ' . $this->getValue($this->compareWith) : '',
            $this->bashEnhancedBrackets ? ConditionalOperator::BRACKET_RIGHT_BASH : ConditionalOperator::BRACKET_RIGHT
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function __toArray(): array
    {
        return [
            'bashBrackets' => $this->bashEnhancedBrackets,
            'negate' => $this->negateExpression,
            'compare' => $this->getValueDebug($this->compare),
            'operator' => $this->operator,
            'compareWith' => $this->getValueDebug($this->compareWith),
        ];
    }
}
