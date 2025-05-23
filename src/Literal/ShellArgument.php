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
    protected const IS_ARGUMENT = true;

    protected string $delimiter = '';

    public function __construct(ShellInterface|string $argument)
    {
        parent::__construct('', $argument);
    }

    protected function validate(): void
    {
        if (!$this->value) {
            throw new ShellBuilderException('Argument cant be empty');
        }

        parent::validate();
    }
}
