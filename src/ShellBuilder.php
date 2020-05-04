<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

use PHPSu\ShellCommandBuilder\Collection\CollectionTuple;
use PHPSu\ShellCommandBuilder\Collection\Pipeline;
use PHPSu\ShellCommandBuilder\Collection\ShellList;
use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class ShellBuilder implements ShellInterface
{
    /** @var array<ShellCommand|ShellBuilder|CollectionTuple>  */
    private $commandList = [];
    /** @var int */
    private $groupType;

    public function __construct(int $groupType = GroupType::NO_GROUP)
    {
        $this->groupType = $groupType;
    }

    public function createCommand(string $name, bool $withNewBuilder = false): ShellCommand
    {
        return new ShellCommand($name, $withNewBuilder ? new self() : $this);
    }

    public function add($command): self
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        $this->validateCommand($command, true);
        if (empty($this->commandList)) {
            $this->commandList[] = $command;
            return $this;
        }
        $list = new ShellList();
        $list->add($command);
        $this->commandList[] = $list;
        return $this;
    }

    public function and($command): self
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        $this->validateCommand($command);
        $list = new ShellList();
        $list->addAnd($command);
        $this->commandList[] = $list;
        return $this;
    }

    public function or($command): self
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        $this->validateCommand($command);
        $list = new ShellList();
        $list->addOr($command);
        $this->commandList[] = $list;
        return $this;
    }

    public function pipe($command): self
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        $this->validateCommand($command);
        $list = new Pipeline();
        $list->pipe($command);
        $this->commandList[] = $list;
        return $this;
    }

    public function pipeWithForward($command): self
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }
        $this->validateCommand($command);
        $list = new Pipeline();
        $list->pipeErrorForward($command);
        $this->commandList[] = $list;
        return $this;
    }

    public function createGroup(bool $inSameShell = false): self
    {
        return new self($inSameShell ? GroupType::SAMESHELL_GROUP : GroupType::SUBSHELL_GROUP);
    }

    private function validateCommand($command, bool $allowEmpty = false): void
    {
        if (!($command instanceof ShellInterface)) {
            throw new ShellBuilderException('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        }
        if (!$allowEmpty && empty($this->commandList)) {
            throw new ShellBuilderException('You have to first add a command before you can combine it');
        }
    }

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
        foreach ($this->commandList as $command) {
            $result .= $command;
        }
        if ($this->groupType === GroupType::SAMESHELL_GROUP) {
            return sprintf(
                '%s%s;%s',
                ControlOperator::CURLY_BLOCK_DEFINITON_OPEN,
                trim($result),
                ControlOperator::CURLY_BLOCK_DEFINITON_CLOSE
            );
        }
        if ($this->groupType === GroupType::SUBSHELL_GROUP) {
            return sprintf(
                '%s%s%s',
                ControlOperator::BLOCK_DEFINITON_OPEN,
                trim($result),
                ControlOperator::BLOCK_DEFINITON_CLOSE
            );
        }
        return trim($result);
    }
}
