<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Definition\Pattern;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * Representing the basic element of a Shell Command, a single literal aka "word"
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 * @package PHPSu\ShellCommandBuilder\Literal
 */
class ShellWord implements ShellInterface
{
    protected const OPTION_CONTROL = '--';

    protected const SHORT_OPTION_CONTROL = '-';

    protected const EQUAL_CONTROL = '=';

    protected const IS_SHORT_OPTION = false;

    protected const IS_OPTION = false;

    protected const IS_ARGUMENT = false;

    protected const IS_ENVIRONMENT_VARIABLE = false;

    protected const IS_VARIABLE = false;

    protected bool $isEscaped = true;

    protected bool $spaceAfterValue = true;

    protected bool $useAssignOperator = false;

    protected bool $nameUpperCase = false;

    protected bool $wrapAsSubcommand = false;

    protected bool $wrapWithBacktricks = false;

    protected string $prefix = '';

    protected string $suffix = ' ';

    protected string $delimiter = ' ';

    protected string $argument;


    /**
     * The constructor is protected, you must choose one of the children
     * @throws ShellBuilderException
     */
    protected function __construct(string $argument, protected ShellInterface|string $value = '')
    {
        if ($argument !== '' && $argument !== '0' && !$this->validShellWord($argument)) {
            throw new ShellBuilderException(
                'A Shell Argument has to be a valid Shell word and cannot contain e.g whitespace'
            );
        }

        $this->argument = $argument;
    }

    public function setEscape(bool $isEscaped): self
    {
        $this->isEscaped = $isEscaped;
        return $this;
    }

    public function setSpaceAfterValue(bool $spaceAfterValue): self
    {
        $this->spaceAfterValue = $spaceAfterValue;
        return $this;
    }

    public function setAssignOperator(bool $useAssignOperator): self
    {
        $this->useAssignOperator = $useAssignOperator;
        return $this;
    }

    public function setNameUppercase(bool $uppercaseName): self
    {
        $this->nameUpperCase = $uppercaseName;
        return $this;
    }

    protected function validate(): void
    {
    }

    /**
     * @throws ShellBuilderException
     */
    private function validShellWord(string $word): bool
    {
        return count(Pattern::split($word)) === 1;
    }

    private function prepare(): void
    {
        $this->validate();
        if (!$this->spaceAfterValue) {
            $this->suffix = '';
        }

        if ($this->useAssignOperator) {
            $this->delimiter = self::EQUAL_CONTROL;
        }

        if ($this->argument && $this->nameUpperCase) {
            $this->argument = strtoupper($this->argument);
        }
    }

    /**
     * @return array<mixed>|string
     */
    private function getValue(bool $debug = false): array|string
    {
        $word = $this->value;
        if ($word instanceof ShellInterface) {
            if ($debug) {
                return $word->__toArray();
            }

            $word = (string)$word;
        }

        if ($this->isEscaped && ($word !== '' && $word !== '0')) {
            return escapeshellarg($word);
        }

        return $word;
    }

    /**
     * @return array<string, mixed>
     */
    public function __toArray(): array
    {
        $this->prepare();
        return [
            'isArgument' => static::IS_ARGUMENT,
            'isShortOption' => static::IS_SHORT_OPTION,
            'isOption' => static::IS_OPTION,
            'isEnvironmentVariable' => static::IS_ENVIRONMENT_VARIABLE,
            'isVariable' => static::IS_VARIABLE,
            'escaped' => $this->isEscaped,
            'withAssign' => $this->useAssignOperator,
            'spaceAfterValue' => $this->spaceAfterValue,
            'value' => $this->getValue(true),
            'argument' => $this->argument,
        ];
    }

    public function __toString(): string
    {
        $this->prepare();
        /** @var string $value */
        $value = $this->getValue();
        if ($this->value instanceof ShellInterface && $this->wrapAsSubcommand) {
            $value = $this->wrapWithBacktricks ? sprintf('`%s`', $value) : sprintf('$(%s)', $value);
        }

        return sprintf(
            '%s%s%s%s%s',
            $this->prefix,
            $this->argument,
            $this->delimiter,
            $value,
            $this->suffix
        );
    }
}
