<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class ShellCommand implements ShellInterface
{
    /** @var string */
    private $executable;
    /** @var array<ShellWord> */
    private $arguments = [];
    /** @var array<ShellWord> */
    private $environmentVariables = [];
    /** @var bool  */
    private $isCommandSubstitution = false;
    /** @var ShellBuilder|null */
    private $parentBuilder;

    public function __construct(string $name, ShellBuilder $builder = null)
    {
        $this->executable = $name;
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
        $word = new ShellWord($option, $value);
        $word->asShortOption();
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
        $word = new ShellWord($option, $value);
        $word->asOption();
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
        $word = new ShellWord($argument);
        $word->asArgument();
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
        $word = new ShellWord($argument);
        $word->asArgument()->setSpaceAfterValue(false);
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
        $word = new ShellWord($name, $value);
        $word->asEnvironmentVariable();
        $word->setAssignOperator(true);
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
            'executable' => $this->executable,
            'arguments' => $commands,
            'isCommandSubstitution' => $this->isCommandSubstitution,
            'environmentVariables' => $envs,
        ];
    }

    public function __toString(): string
    {
        $result = (sprintf(
            '%s%s%s',
            empty($this->environmentVariables) ? '' : $this->environmentVariablesToString(),
            $this->executable,
            empty($this->arguments) ? '' : ' ' . $this->argumentsToString()
        ));
        if ($this->isCommandSubstitution) {
            return sprintf("\$(%s)", $result);
        }
        return $result;
    }
}
