<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use JsonSerializable;
use PHPSu\ShellCommandBuilder\Collection\CollectionTuple;
use PHPSu\ShellCommandBuilder\Collection\Pipeline;
use PHPSu\ShellCommandBuilder\Collection\Redirection;
use PHPSu\ShellCommandBuilder\Collection\ShellList;
use PHPSu\ShellCommandBuilder\Conditional\BasicExpression;
use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\Literal\ShellVariable;
use TypeError;

final class ShellBuilder implements ShellInterface, JsonSerializable
{
    use ShellConditional;

    /** @var array<ShellInterface|string>  */
    private array $commandList = [];

    /**
     * name of the coprocess - empty string means anonymous
     */
    private ?string $asynchronously = null;

    private bool $processSubstitution = false;

    private bool $commandSubstitution = false;

    /** @var array<string, ShellVariable> */
    private array $variables = [];

    /**
     * This is a shortcut for quicker fluid access to the shell builder
     */
    public static function new(): self
    {
        return new ShellBuilder();
    }

    /**
     * This is a shortcut for quicker fluid access to the command api
     */
    public static function command(string $executable): ShellCommand
    {
        return new ShellCommand($executable, new self());
    }

    public function __construct(private readonly int $groupType = GroupType::NO_GROUP)
    {
    }

    public function createCommand(string $name, bool $withNewBuilder = false): ShellCommand
    {
        return new ShellCommand($name, $withNewBuilder ? new self() : $this);
    }

    public function runAsynchronously(bool $isAsync = true, string $name = ''): self
    {
        $this->asynchronously = $isAsync ? $name : null;
        return $this;
    }

    /**
     * @param bool $escape is the value instance of ShellInterface, then this variable is automatically false
     * @return $this
     * @throws ShellBuilderException
     */
    public function addVariable(string $variable, ShellInterface|string $value, bool $useBackticks = false, bool $escape = true, bool $noSemicolon = false): self
    {
        if (isset($this->variables[$variable])) {
            throw new ShellBuilderException('Variable has already been declared.');
        }

        $shellVariable = new ShellVariable($variable, $value);
        $shellVariable->wrapWithBackticks($useBackticks);
        $shellVariable->setNoSemicolon($noSemicolon);
        if (is_string($value)) {
            $shellVariable->setEscape($escape);
        }

        $this->variables[$variable] = $shellVariable;
        return $this;
    }

    public function removeVariable(string $variable): self
    {
        unset($this->variables[$variable]);
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function add(ShellInterface|string ...$commands): self
    {
        foreach ($commands as $command) {
            $this->addSingle($command);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function addSingle(ShellInterface|string $command, bool $raw = false): self
    {
        $command = $raw ? $command : $this->parseCommand($command, true);
        if ($this->commandList === []) {
            $this->commandList[] = $command;
            return $this;
        }

        $this->commandList[] = ShellList::add($command);
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function and(ShellInterface|string $command): self
    {
        $this->commandList[] = ShellList::addAnd($this->parseCommand($command));
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function or(ShellInterface|string $command): self
    {
        $this->commandList[] = ShellList::addOr($this->parseCommand($command));
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function async(ShellInterface|string $command = ''): self
    {
        $this->commandList[] = ShellList::async($this->parseCommand($command));
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipe(ShellInterface|string $command): self
    {
        $this->commandList[] = Pipeline::pipe($this->parseCommand($command));
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipeWithForward(ShellInterface|string $command): self
    {
        $this->commandList[] = Pipeline::pipeErrorForward($this->parseCommand($command));
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectOutput(ShellInterface|string $command, bool $append = false): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectOutput($command, $append);
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectInput(ShellInterface|string $command): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectInput($command);
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectError(ShellInterface|string $command): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectError($command);
        return $this;
    }

    /**
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirect(ShellInterface|string $command, bool $toLeft = true): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectBetweenFiles($command, $toLeft);
        return $this;
    }

    /**
     * @throws ShellBuilderException
     */
    public function redirectDescriptor(ShellInterface|string $command, bool $toLeft, ?int $firstDescriptor = null, ?int $secondDescriptor = null): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectBetweenDescriptors($command, $toLeft, $firstDescriptor, $secondDescriptor);
        return $this;
    }

    public function redirectErrorToOutput(): self
    {
        $this->commandList[] = Redirection::redirectErrorToOutput();
        return $this;
    }

    public function addCondition(BasicExpression $condition): self
    {
        $this->commandList[] = $condition;
        return $this;
    }

    public function addFileEnding(ShellInterface|string $fileEnding): self
    {
        $tuple = CollectionTuple::create($fileEnding, '.');
        $tuple
            ->noSpaceAfterJoin(true)
            ->noSpaceBeforeJoin(true);
        $this->commandList[] = $tuple;
        return $this;
    }

    public function createGroup(bool $inSameShell = false): self
    {
        return new self($inSameShell ? GroupType::SAMESHELL_GROUP : GroupType::SUBSHELL_GROUP);
    }

    public function createProcessSubstition(): self
    {
        $builder = new self(GroupType::SUBSHELL_GROUP);
        $builder->processSubstitution = true;
        return $builder;
    }

    public function createCommandSubstition(): self
    {
        $builder = new self(GroupType::SUBSHELL_GROUP);
        $builder->commandSubstitution = true;
        return $builder;
    }

    public function hasCommands(): bool
    {
        return $this->commandList !== [] || $this->variables !== [];
    }

    /**
     * @throws ShellBuilderException
     */
    private function parseCommand(ShellInterface|string $command, bool $allowEmpty = false): ShellInterface
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }

        try {
            $this->validateCommand($allowEmpty);
        } catch (TypeError) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }

        return $command;
    }

    private function validateCommand(bool $allowEmpty): void
    {
        if (!$allowEmpty && $this->commandList === []) {
            throw new ShellBuilderException('You have to first add a command before you can combine it');
        }
    }

    private function variablesToString(): string
    {
        $variableString = implode('', $this->variables);

        if ($variableString !== '') {
            $variableString .= ' ';
        }

        return $variableString;
    }

    /**
     * @return list<string|array<mixed>>
     */
    public function jsonSerialize(): array
    {
        return $this->__toArray();
    }

    /**
     * @return list<string|array<mixed>>
     */
    public function __toArray(): array
    {
        $commands = [];
        foreach ($this->commandList as $item) {
            $commands[] = is_string($item) ? $item : $item->__toArray();
        }

        return $commands;
    }

    public function __toString(): string
    {
        $result = '';
        if ($this->asynchronously !== null) {
            $result = sprintf('coproc %s%s', $this->asynchronously, $this->asynchronously !== '' ? ' ' : '');
        }

        $result .= implode('', $this->commandList);

        if ($this->groupType === GroupType::SAMESHELL_GROUP) {
            return sprintf(
                '%s %s;%s',
                ControlOperator::CURLY_BLOCK_DEFINITON_OPEN,
                $result,
                ControlOperator::CURLY_BLOCK_DEFINITON_CLOSE
            );
        }

        if ($this->groupType === GroupType::SUBSHELL_GROUP) {
            $substitionType = '';
            if ($this->commandSubstitution) {
                $substitionType = '$';
            }

            if ($this->processSubstitution) {
                $substitionType = '<';
            }

            return sprintf(
                '%s%s%s%s',
                $substitionType,
                ControlOperator::BLOCK_DEFINITON_OPEN,
                $result,
                ControlOperator::BLOCK_DEFINITON_CLOSE
            );
        }

        return rtrim(sprintf('%s%s', $this->variablesToString(), $result));
    }
}
