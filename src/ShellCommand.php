<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\Literal\ShellArgument;
use PHPSu\ShellCommandBuilder\Literal\ShellEnvironmentVariable;
use PHPSu\ShellCommandBuilder\Literal\ShellExecutable;
use PHPSu\ShellCommandBuilder\Literal\ShellOption;
use PHPSu\ShellCommandBuilder\Literal\ShellShortOption;
use PHPSu\ShellCommandBuilder\Literal\ShellWord;

final class ShellCommand implements ShellInterface
{
    /**
     * @var ShellWord
     * @psalm-readonly
     */
    private $executable;
    /** @var array<ShellWord> */
    private $arguments = [];
    /** @var array<ShellWord> */
    private $environmentVariables = [];
    /** @var bool  */
    private $isCommandSubstitution = false;
    /** @var bool  */
    private $isProcessSubstitution = false;
    /** @var ShellBuilder|null */
    private $parentBuilder;
    /** @var bool */
    private $invertOutput = false;

    public function __construct(string $name, ShellBuilder $builder = null)
    {
        $this->executable = new ShellExecutable($name);
        $this->parentBuilder = $builder;
    }

    public function addToBuilder(): ShellBuilder
    {
        if ($this->parentBuilder === null) {
            throw new ShellBuilderException('You need to create a ShellBuilder first before you can use it within a command');
        }
        return $this->parentBuilder->add($this);
    }

    public function toggleCommandSubstitution(): self
    {
        $this->isCommandSubstitution = !$this->isCommandSubstitution;
        return $this;
    }

    public function isProcessSubstitution(bool $enable = true): self
    {
        $this->isProcessSubstitution = $enable;
        return $this;
    }

    public function invert(bool $invert = true): self
    {
        $this->invertOutput = $invert;
        return $this;
    }

    /**
     * @param string $option
     * @param ShellInterface|string|mixed $value
     * @param bool $escapeArgument
     * @param bool $withAssignOperator
     * @return self
     * @throws ShellBuilderException
     */
    public function addShortOption(string $option, $value = '', bool $escapeArgument = true, bool $withAssignOperator = false): self
    {
        if (!($value instanceof ShellInterface || is_string($value))) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        $word = new ShellShortOption($option, $value);
        return $this->add($word, $escapeArgument, $withAssignOperator);
    }

    /**
     * @param string $option
     * @param ShellInterface|string|mixed $value
     * @param bool $escapeArgument
     * @param bool $withAssignOperator
     * @return self
     * @throws ShellBuilderException
     */
    public function addOption(string $option, $value = '', bool $escapeArgument = true, bool $withAssignOperator = false): self
    {
        if (!($value instanceof ShellInterface || is_string($value))) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        $word = new ShellOption($option, $value);
        return $this->add($word, $escapeArgument, $withAssignOperator);
    }

    /**
     * @param ShellInterface|string|mixed $argument
     * @param bool $escapeArgument
     * @return self
     * @throws ShellBuilderException
     */
    public function addArgument($argument, bool $escapeArgument = true): self
    {
        if (!($argument instanceof ShellInterface || is_string($argument))) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        $word = new ShellArgument($argument);
        return $this->add($word, $escapeArgument);
    }

    /**
     * This is an alias for argument, that automatically escapes the argument.
     * It does in the end does not provide any additional functionality
     *
     * @param ShellInterface $argument
     * @return $this
     * @throws ShellBuilderException
     */
    public function addSubCommand(ShellInterface $argument): self
    {
        return $this->addArgument($argument, true);
    }

    /**
     * @param ShellInterface|string|mixed $argument
     * @return self
     * @throws ShellBuilderException
     */
    public function addNoSpaceArgument($argument): self
    {
        if (!($argument instanceof ShellInterface || is_string($argument))) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        $word = new ShellArgument($argument);
        $word->setSpaceAfterValue(false);
        return $this->add($word, false);
    }

    private function add(ShellWord $word, bool $escapeArgument, bool $withAssignOperator = false): self
    {
        $word->setEscape($escapeArgument);
        $word->setAssignOperator($withAssignOperator);
        $this->arguments[] = $word;
        return $this;
    }

    public function addEnv(string $name, string $value): self
    {
        $word = new ShellEnvironmentVariable($name, $value);
        $this->environmentVariables[$name] = $word;
        return $this;
    }

    private function argumentsToString(): string
    {
        $result = [];
        foreach ($this->arguments as $part) {
            $result[] = $part;
        }
        return trim(implode('', $result));
    }

    private function environmentVariablesToString(): string
    {
        $result = [];
        foreach ($this->environmentVariables as $part) {
            $result[] = $part;
        }
        return implode('', $result);
    }

    /**
     * @return array<string, mixed>
     */
    public function __toArray(): array
    {
        $commands = [];
        foreach ($this->arguments as $item) {
            $commands[] = $item->__toArray();
        }
        $envs = [];
        foreach ($this->environmentVariables as $item) {
            $envs[] = $item->__toArray();
        }
        return [
            'executable' => $this->executable->__toString(),
            'arguments' => $commands,
            'isCommandSubstitution' => $this->isCommandSubstitution,
            'environmentVariables' => $envs,
        ];
    }

    public function __toString(): string
    {
        /** @psalm-suppress ImplicitToStringCast */
        $result = (sprintf(
            '%s%s%s%s',
            $this->invertOutput ? '! ' : '',
            empty($this->environmentVariables) ? '' : $this->environmentVariablesToString(),
            $this->executable,
            empty($this->arguments) ? '' : ' ' . $this->argumentsToString()
        ));
        if ($this->isCommandSubstitution && !$this->isProcessSubstitution) {
            return sprintf("\$(%s)", $result);
        }
        if ($this->isProcessSubstitution && !$this->isCommandSubstitution) {
            return sprintf("<(%s)", $result);
        }
        return $result;
    }
}
