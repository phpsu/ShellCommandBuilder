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

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellCommand implements ShellInterface
{
    use ShellConditional;

    /**
     * @psalm-readonly
     */
    private ShellExecutable $executable;

    /** @var array<ShellWord> */
    private array $arguments = [];

    /** @var array<ShellWord> */
    private array $environmentVariables = [];

    private bool $isCommandSubstitution = false;

    private bool $isProcessSubstitution = false;


    private bool $invertOutput = false;

    public function __construct(string $name, private readonly ?ShellBuilder $parentBuilder = null)
    {
        $this->executable = new ShellExecutable($name);
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
     * @throws ShellBuilderException
     */
    public function addShortOption(string $option, ShellInterface|string $value = '', bool $escapeArgument = true, bool $withAssignOperator = false): self
    {
        $word = new ShellShortOption($option, $value);
        return $this->add($word, $escapeArgument, $withAssignOperator);
    }

    /**
     * @throws ShellBuilderException
     */
    public function addOption(string $option, ShellInterface|string $value = '', bool $escapeArgument = true, bool $withAssignOperator = false): self
    {
        $word = new ShellOption($option, $value);
        return $this->add($word, $escapeArgument, $withAssignOperator);
    }

    /**
     * @throws ShellBuilderException
     * @return $this
     */
    public function addArgument(ShellInterface|string $argument, bool $escapeArgument = true): self
    {
        $word = new ShellArgument($argument);
        return $this->add($word, $escapeArgument);
    }

    /**
     * This is an alias for argument, that automatically escapes the argument.
     * It does in the end does not provide any additional functionality
     *
     * @return $this
     * @throws ShellBuilderException
     */
    public function addSubCommand(ShellInterface $argument): self
    {
        return $this->addArgument($argument, true);
    }

    /**
     * @throws ShellBuilderException
     */
    public function addNoSpaceArgument(ShellInterface|string $argument): self
    {
        $word = new ShellArgument($argument);
        $word->setSpaceAfterValue(false);
        return $this->add($word, false);
    }

    /**
     * @return $this
     */
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
        $result = $this->arguments;
        return trim(implode('', $result));
    }

    private function environmentVariablesToString(): string
    {
        $result = $this->environmentVariables;
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
            $this->environmentVariables === [] ? '' : $this->environmentVariablesToString(),
            $this->executable,
            $this->arguments === [] ? '' : ' ' . $this->argumentsToString()
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
