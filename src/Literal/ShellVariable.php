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
    /** @var bool */
    private $noSemicolon = false;

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

    public function setNoSemicolon(bool $noSemicolon): self
    {
        $this->noSemicolon = $noSemicolon;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s%s', parent::__toString(), $this->noSemicolon ? '' : ';');
    }
}
