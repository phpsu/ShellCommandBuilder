<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellOption extends ShellWord
{
    protected const IS_OPTION = true;

    protected string $prefix = ShellWord::OPTION_CONTROL;

    /**
     * ShellArgument constructor.
     * @throws ShellBuilderException
     */
    public function __construct(string $option, ShellInterface|string $value = '')
    {
        if (!$value) {
            $this->delimiter = '';
        }

        parent::__construct($option, $value);
    }
}
