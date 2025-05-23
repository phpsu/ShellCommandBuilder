<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class ShellExecutable extends ShellWord
{
    protected const IS_ARGUMENT = true;

    protected bool $spaceAfterValue = false;

    protected bool $isEscaped = false;

    protected string $delimiter = '';

    protected string $suffix = '';

    public function __construct(string $executable)
    {
        parent::__construct($executable);
    }
}
