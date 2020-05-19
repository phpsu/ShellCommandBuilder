<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Collection\CollectionTuple;
use PHPSu\ShellCommandBuilder\Collection\Pipeline;
use PHPSu\ShellCommandBuilder\Collection\Redirection;
use PHPSu\ShellCommandBuilder\Collection\ShellList;
use PHPSu\ShellCommandBuilder\Conditional\BasicExpression;
use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use TypeError;

final class ShellBuilder implements ShellInterface
{
    /** @var array<ShellInterface|CollectionTuple>  */
    private $commandList = [];
    /** @var int */
    private $groupType;
    /** @var bool */
    private $asynchronously = false;

    /**
     * This is a shortcut for quicker fluid access to the shell builder
     * @return static
     */
    public static function new(): self
    {
        return new ShellBuilder();
    }

    public function __construct(int $groupType = GroupType::NO_GROUP)
    {
        $this->groupType = $groupType;
    }

    public function createCommand(string $name, bool $withNewBuilder = false): ShellCommand
    {
        return new ShellCommand($name, $withNewBuilder ? new self() : $this);
    }

    public function runAsynchronously(bool $isAsync = true): self
    {
        $this->asynchronously = $isAsync;
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function add($command): self
    {
        $command = $this->parseCommand($command, true);
        if (empty($this->commandList)) {
            $this->commandList[] = $command;
            return $this;
        }
        $this->commandList[] = ShellList::add($command);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function and($command): self
    {
        $this->commandList[] = ShellList::addAnd($this->parseCommand($command));
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function or($command): self
    {
        $this->commandList[] = ShellList::addOr($this->parseCommand($command));
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function async($command): self
    {
        $this->commandList[] = ShellList::async($this->parseCommand($command));
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipe($command): self
    {
        $this->commandList[] = Pipeline::pipe($this->parseCommand($command));
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function pipeWithForward($command): self
    {
        $this->commandList[] = Pipeline::pipeErrorForward($this->parseCommand($command));
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @param bool $append
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectOutput($command, bool $append = false): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectOutput($command, $append);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectInput($command): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectInput($command);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectError($command): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectError($command);
        return $this;
    }

    /**
     * @param string|ShellInterface $command
     * @param bool $toLeft
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirect($command, bool $toLeft = true): self
    {
        $command = $this->parseCommand($command);
        $this->commandList[] = Redirection::redirectBetweenFiles($command, $toLeft);
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

    public function createGroup(bool $inSameShell = false): self
    {
        return new self($inSameShell ? GroupType::SAMESHELL_GROUP : GroupType::SUBSHELL_GROUP);
    }

    /**
     * @param string|ShellInterface $command
     * @param bool $allowEmpty
     * @return ShellInterface
     * @throws ShellBuilderException
     */
    private function parseCommand($command, bool $allowEmpty = false): ShellInterface
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        try {
            $this->validateCommand($command, $allowEmpty);
        } catch (TypeError $typeError) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        return $command;
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function validateCommand(ShellInterface $command, bool $allowEmpty): void
    {
        if (!$allowEmpty && empty($this->commandList)) {
            throw new ShellBuilderException('You have to first add a command before you can combine it');
        }
    }

    /**
     * @return array<mixed>
     */
    public function __toArray(): array
    {
        $commands = [];
        foreach ($this->commandList as $item) {
            $commands[] = $item instanceof ShellInterface ? $item->__toArray() : $item;
        }
        return $commands;
    }

    public function __toString(): string
    {
        $result = '';
        if ($this->asynchronously) {
            $result = 'coproc ';
        }
        foreach ($this->commandList as $command) {
            $result .= $command;
        }
        if ($this->groupType === GroupType::SAMESHELL_GROUP) {
            return sprintf(
                '%s%s;%s',
                ControlOperator::CURLY_BLOCK_DEFINITON_OPEN,
                $result,
                ControlOperator::CURLY_BLOCK_DEFINITON_CLOSE
            );
        }
        if ($this->groupType === GroupType::SUBSHELL_GROUP) {
            return sprintf(
                '%s%s%s',
                ControlOperator::BLOCK_DEFINITON_OPEN,
                $result,
                ControlOperator::BLOCK_DEFINITON_CLOSE
            );
        }
        return $result;
    }
}
