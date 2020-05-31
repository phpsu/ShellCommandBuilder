<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Definition\Pattern;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * Representing the basic element of a Shell Command, a single literal aka "word"
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder\Literal
 * @package PHPSu\ShellCommandBuilder\Literal
 */
class ShellWord implements ShellInterface
{
    protected const OPTION_CONTROL = '--';
    protected const SHORT_OPTION_CONTROL = '-';
    protected const EQUAL_CONTROL = '=';

    /**
     * @var bool
     * @psalm-readonly
     */
    protected $isShortOption = false;
    /**
     * @var bool
     * @psalm-readonly
     */
    protected $isOption = false;
    /**
     * @var bool
     * @psalm-readonly
     */
    protected $isArgument = false;
    /**
     * @var bool
     * @psalm-readonly
     */
    protected $isEnvironmentVariable = false;
    /**
     * @var bool
     * @psalm-readonly
     */
    protected $isVariable = false;
    /** @var bool */
    protected $isEscaped = true;
    /** @var bool */
    protected $spaceAfterValue = true;
    /** @var bool */
    protected $useAssignOperator = false;
    /** @var bool */
    protected $nameUpperCase = false;
    /** @var bool */
    protected $wrapAsSubcommand = false;
    /** @var bool */
    protected $wrapWithBacktricks = false;
    /** @var string */
    protected $prefix = '';
    /** @var string */
    protected $suffix = ' ';
    /** @var string */
    protected $delimiter = ' ';
    /** @var string */
    protected $argument;
    /** @var string|ShellInterface */
    protected $value;

    /**
     * The constructor is protected, you must choose one of the children
     * @param string $argument
     * @param string|ShellInterface $value
     * @throws ShellBuilderException
     */
    protected function __construct(string $argument, $value = '')
    {
        if (!empty($argument) && !$this->validShellWord($argument)) {
            throw new ShellBuilderException(
                'A Shell Argument has to be a valid Shell word and cannot contain e.g whitespace'
            );
        }
        $this->argument = $argument;
        $this->value = $value;
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
        /** @psalm-suppress DocblockTypeContradiction */
        if (!(is_string($this->value) || $this->value instanceof ShellInterface)) {
            throw new ShellBuilderException('Value must be an instance of ShellInterface or a string');
        }
    }

    /**
     * @psalm-pure
     * @param string $word
     * @return bool
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
        if (!empty($this->argument) && $this->nameUpperCase) {
            $this->argument = strtoupper($this->argument);
        }
    }

    /**
     * @param bool $debug
     * @return array<mixed>|string
     */
    private function getValue(bool $debug = false)
    {
        $word = $this->value;
        if ($word instanceof ShellInterface) {
            if ($debug) {
                return $word->__toArray();
            }
            $word = (string)$word;
        }
        if ($this->isEscaped && !empty($word)) {
            $word = escapeshellarg($word);
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
            'isArgument' => $this->isArgument,
            'isShortOption' => $this->isShortOption,
            'isOption' => $this->isOption,
            'isEnvironmentVariable' => $this->isEnvironmentVariable,
            'isVariable' => $this->isVariable,
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
            $value = $this->wrapWithBacktricks ? "`$value`" : "$($value)";
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
