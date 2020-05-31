<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class ShellVariable extends ShellWord
{
    protected $isVariable = true;
    protected $useAssignOperator = true;
    protected $wrapAsSubcommand = true;
    protected $spaceAfterValue = false;

    /**
     * ShellVariable constructor.
     * @param string $option
     * @param ShellInterface|string $value
     * @throws ShellBuilderException
     */
    public function __construct(string $option, $value)
    {
        parent::__construct($option, $value);
        if ($this->value instanceof ShellInterface) {
            $this->setEscape(false);
        }
    }

    public function wrapWithBackticks(bool $enable): self
    {
        $this->wrapWithBacktricks = $enable;
        return $this;
    }
}
