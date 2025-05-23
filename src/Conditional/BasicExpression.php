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
    /** @var bool this is always double-quoted */
    protected bool $escapedValue = false;


    protected ShellInterface|string $compare = '';

    protected ShellInterface|string $compareWith = '';

    protected string $operator = '';

    public function __construct(
        /**
         * This is not POSIX-compatible (only eg. Korn and Bash), beware of using it
         */
        protected bool $bashEnhancedBrackets,
        private readonly bool $negateExpression
    ) {
    }

    public function escapeValue(bool $enable): BasicExpression
    {
        $this->escapedValue = $enable;
        return $this;
    }

    abstract public static function create(bool $useBashBrackets = false, bool $negateExpression = false): static;


    /**
     * @return string|array<mixed>
     */
    private function getValueDebug(ShellInterface|string $value): array|string
    {
        if ($value instanceof ShellInterface) {
            return $value->__toArray();
        }

        return $value;
    }

    private function getValue(ShellInterface|string $value): string
    {
        if ($this->escapedValue) {
            /** @psalm-suppress ImplicitToStringCast */
            return sprintf('"%s"', $value);
        }

        return (string)$value;
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
