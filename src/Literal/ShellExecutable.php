<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Literal;

final class ShellExecutable extends ShellWord
{
    protected $isArgument = true;
    protected $spaceAfterValue = false;
    protected $isEscaped = false;
    protected $delimiter = '';
    protected $prefix = '';
    protected $suffix = '';

    public function __construct(string $executable)
    {
        parent::__construct($executable);
    }
}
