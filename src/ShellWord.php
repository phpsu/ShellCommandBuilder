<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class ShellWord implements ShellInterface
{
    private const OPTION_CONTROL = '--';
    private const SHORT_OPTION_CONTROL = '-';
    private const EQUAL_CONTROL = '=';

    /** @var bool  */
    private $isShortOption = false;
    /** @var bool  */
    private $isOption = false;
    /** @var bool  */
    private $isArgument = false;
    /** @var bool  */
    private $isEnvironmentVariable = false;
    /** @var bool  */
    private $isEscaped = true;
    /** @var bool  */
    private $spaceAfterValue = true;
    /** @var bool  */
    private $useAssignOperator = false;

    /** @var string */
    private $prefix = '';
    /** @var string */
    private $suffix = ' ';
    /** @var string */
    private $delimiter = ' ';
    /** @var string|ShellInterface */
    private $argument;
    /** @var string|ShellInterface */
    private $value;

    /**
     * ShellWord constructor.
     * @param string|ShellInterface $argument
     * @param string|ShellInterface $value
     */
    public function __construct($argument, $value = '')
    {
        $this->argument = $argument;
        $this->value = $value;
    }

    public function asOption(): self
    {
        $this->reset();
        $this->isOption = true;
        return $this;
    }

    public function asShortOption(): self
    {
        $this->reset();
        $this->isShortOption = true;
        return $this;
    }

    public function asArgument(): self
    {
        $this->reset();
        $this->isArgument = true;
        return $this;
    }

    public function asEnvironmentVariable(): self
    {
        $this->reset();
        $this->isEnvironmentVariable = true;
        return $this;
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

    private function reset(): void
    {
        $this->isOption = false;
        $this->isShortOption = false;
        $this->isArgument = false;
    }

    private function validate(): void
    {
        if ($this->argument === null || (is_string($this->argument) && empty($this->argument))) {
            throw new ShellBuilderException('Argument cant be empty');
        }
        if (!(is_string($this->value) || $this->value instanceof ShellInterface)) {
            throw new ShellBuilderException('Value must be an instance of ShellInterface or a string');
        }
        if (!(is_string($this->argument) || $this->argument instanceof ShellInterface)) {
            throw new ShellBuilderException('Argument must be an instance of ShellInterface or a string');
        }
        if (!($this->isOption || $this->isShortOption || $this->isArgument || $this->isEnvironmentVariable)) {
            throw new ShellBuilderException('No ShellWord-Type defined - use e.g. asArgument() to define it');
        }
        if ($this->isArgument && is_string($this->value) && !empty($this->value)) {
            throw new ShellBuilderException('An argument cant have a value');
        }
        if ($this->isArgument && $this->value instanceof ShellInterface) {
            throw new ShellBuilderException('An argument cant have a value');
        }
        if ($this->isEnvironmentVariable && !(is_string($this->value) && is_string($this->argument))) {
            throw new ShellBuilderException('EnvironmentVariables must be string only');
        }
    }

    private function prepare(): void
    {
        if ($this->isOption) {
            $this->prefix = self::OPTION_CONTROL;
        }
        if ($this->isShortOption) {
            $this->prefix = self::SHORT_OPTION_CONTROL;
        }
        if (!$this->spaceAfterValue) {
            $this->suffix = '';
        }
        if ($this->useAssignOperator) {
            $this->delimiter = self::EQUAL_CONTROL;
        }
        if ($this->isArgument) {
            $this->delimiter = '';
        }
        if (($this->isShortOption || $this->isOption) && empty($this->value)) {
            $this->delimiter = '';
        }
        if ($this->isEnvironmentVariable) {
            $this->argument = strtoupper($this->argument);
        }
    }

    /**
     * @param bool $debug
     * @return array<mixed>|string
     */
    private function getArgument(bool $debug = false)
    {
        $word = $this->argument;
        if ($word instanceof ShellInterface) {
            if ($debug) {
                return $word->__toArray();
            }
            $word = (string)$word;
        }
        if ($this->isEscaped && $this->isArgument && !empty($word)) {
            $word = escapeshellarg($word);
        }
        return $word;
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
     * @throws ShellBuilderException
     */
    public function __toArray(): array
    {
        $this->validate();
        return [
            'isArgument' => $this->isArgument,
            'isShortOption' => $this->isShortOption,
            'isOption' => $this->isOption,
            'isEnvironmentVariable' => $this->isEnvironmentVariable,
            'escaped' => $this->isEscaped,
            'withAssign' => $this->useAssignOperator,
            'spaceAfterValue' => $this->spaceAfterValue,
            'value' => $this->getValue(true),
            'argument' => $this->getArgument(true),
        ];
    }

    public function __toString(): string
    {
        $this->validate();
        $this->prepare();
        /** @var string $argument */
        $argument = $this->getArgument();
        /** @var string $value */
        $value = $this->getValue();
        return sprintf(
            '%s%s%s%s%s',
            $this->prefix,
            $argument,
            $this->delimiter,
            $value,
            $this->suffix
        );
    }
}
