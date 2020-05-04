<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class ShellCommand implements ShellInterface
{
    /** @var string */
    private $executable;
    /** @var array<array<string|ShellInterface>> */
    private $arguments = [];
    /** @var array<string,string> */
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
        if ($escapeArgument && is_string($value) && !empty($value)) {
            $value = escapeshellarg($value);
        }
        return $this->add($option, $value, '-', $withAssignOperator ? '=' : ' ');
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
        if ($escapeArgument && is_string($value) && !empty($value)) {
            $value = escapeshellarg($value);
        }
        return $this->add($option, $value, '--', $withAssignOperator ? '=' : ' ');
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
        if ($escapeArgument && is_string($argument)) {
            $argument = escapeshellarg($argument);
        }
        return $this->add($argument, '', '');
    }

    public function addSubCommand(ShellInterface $argument): self
    {
        return $this->add($argument, '', 'subcommand');
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
        return $this->add($argument, '', '#NOSPACE#');
    }

    /**
     * @param string|ShellInterface $argument
     * @param string|ShellInterface $value
     * @param string $prefix
     * @param string $suffix
     * @return self
     */
    private function add($argument, $value = '', string $prefix = '', string $suffix = ' '): self
    {
        $this->arguments[] = [$prefix, $argument, $suffix, $value];
        return $this;
    }

    public function addEnv(string $name, string $value): self
    {
        $this->environmentVariables[$name] = $value;
        return $this;
    }

    private function argumentsToString(): string
    {
        $result = [];
        foreach ($this->arguments as $part) {
            [$prefix, $argument, $suffix, $value] = $part;
            if ($prefix === 'subcommand') {
                $argument = escapeshellarg((string)$argument);
                $prefix = '';
            }
            if ($value) {
                $value = $suffix . $value;
            }
            $result[] = implode('', [$prefix, $argument, $value]);
        }
        return str_replace(' #NOSPACE#', '', implode(' ', $result));
    }

    private function environmentVariablesToString(): string
    {
        $envs = [];
        foreach ($this->environmentVariables as $key => $variable) {
            $envs[] = sprintf('%s=%s', strtoupper($key), escapeshellarg($variable));
        }
        return implode(' ', $envs);
    }

    /**
     * @return array<string, mixed>
     */
    public function __toArray(): array
    {
        $commands = [];
        foreach ($this->arguments as $item) {
            [$prefix, $argument, $suffix, $value] = $item;
            $commands[] = [
                'prefix' => $prefix,
                'argument' => $argument instanceof ShellInterface ? $argument->__toArray() : $argument,
                'suffix' => $suffix,
                'value' => $value instanceof ShellInterface ? $value->__toArray() : $value,
            ];
        }
        return [
            'executable' => $this->executable,
            'arguments' => $commands,
            'isCommandSubstitution' => $this->isCommandSubstitution,
            'environmentVariables' => $this->environmentVariables,
        ];
    }

    public function __toString(): string
    {
        $result = (sprintf(
            '%s%s%s',
            empty($this->environmentVariables) ? '' : $this->environmentVariablesToString() . ' ',
            $this->executable,
            empty($this->arguments) ? '' : ' ' . $this->argumentsToString()
        ));
        if ($this->isCommandSubstitution) {
            return sprintf("\$(%s)", $result);
        }
        return $result;
    }
}
