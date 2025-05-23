<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Definition;

final class ControlOperator
{
    public const AND_OPERATOR = '&&';

    public const OR_OPERATOR = '||';

    public const BASH_AMPERSAND = '&';

    public const COMMAND_DELIMITER = ';';

    public const DOUBLE_SEMICOLON = ';;';

    public const BLOCK_DEFINITON_OPEN = '(';

    public const BLOCK_DEFINITON_CLOSE = ')';

    public const CURLY_BLOCK_DEFINITON_OPEN = '{';

    public const CURLY_BLOCK_DEFINITON_CLOSE = '}';

    public const PIPELINE = '|';

    public const PIPELINE_WITH_STDERR_FORWARD = '|&';
}
