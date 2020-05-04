<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class ShellCommand implements ShellInterface
{
    /** @var string */
    private $executable;
    /** @var array */
    private $arguments = [];
    /** @var bool  */
    private $isCommandSubstitution = false;
    /** @var ShellBuilder */
    private $parentBuilder;

    public function __construct(string $name, ShellBuilder $builder = null)
    {
        $this->executable = $name;
        $this->parentBuilder = $builder;
    }

    public function getBuilder(): ShellBuilder
    {
        return $this->parentBuilder;
    }

    public function addToBuilder(): ShellBuilder
    {
        return $this->getBuilder()->add($this);
    }

    public function toggleCommandSubstitution(): self
    {
        $this->isCommandSubstitution = !$this->isCommandSubstitution;
        return $this;
    }

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

    public function addNoSpaceArgument($argument): self
    {
        if (!($argument instanceof ShellInterface || is_string($argument))) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        return $this->add($argument, '', '#NOSPACE#');
    }

    private function add($argument, $value = '', string $prefix = '', string $suffix = ' '): self
    {
        $this->arguments[] = [$prefix, $argument, $suffix, $value];
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
        ];
    }

    public function __toString(): string
    {
        $result = (sprintf(
            '%s%s',
            $this->executable,
            empty($this->arguments) ? '' : ' ' . ($this->argumentsToString())
        ));
        if ($this->isCommandSubstitution) {
            return sprintf("\$(%s)", trim($result));
        }
        return trim($result);
    }
}
