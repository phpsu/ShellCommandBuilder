<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellEnvironmentVariable extends ShellWord
{
    protected const IS_ENVIRONMENT_VARIABLE = true;

    protected bool $useAssignOperator = true;

    protected bool $nameUpperCase = true;

    /**
     * ShellArgument constructor.
     * @throws ShellBuilderException
     */
    public function __construct(string $option, ShellInterface|string $value)
    {
        parent::__construct($option, $value);
    }
}
