<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellArgument extends ShellWord
{
    protected $isArgument = true;
    protected $delimiter = '';

    /**
     * ShellArgument constructor.
     * @param ShellInterface|string $argument
     */
    public function __construct($argument)
    {
        parent::__construct('', $argument);
    }

    protected function validate(): void
    {
        if (is_string($this->value) && empty($this->value)) {
            throw new ShellBuilderException('Argument cant be empty');
        }
        parent::validate();
    }
}
