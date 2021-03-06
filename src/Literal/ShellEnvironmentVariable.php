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
    protected $isEnvironmentVariable = true;
    protected $useAssignOperator = true;
    protected $nameUpperCase = true;

    /**
     * ShellArgument constructor.
     * @param string $option
     * @param ShellInterface|string $value
     * @throws ShellBuilderException
     */
    public function __construct(string $option, $value)
    {
        parent::__construct($option, $value);
    }
}
