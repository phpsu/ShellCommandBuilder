<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Definition;

/**
 * @see https://www.gnu.org/software/bash/manual/html_node/Redirections.html#Redirections
 */
final class RedirectOperator
{
    public const STDOUT_LEFT_INSERT = '>';

    public const STDOUT_LEFT_INSERT_NEWFILE = '>|';

    public const STDOUT_LEFT_APPEND = '>>';

    public const STDIN_RIGHT = '<';

    public const FILE_DESCRIPTOR_IN = 0;

    public const FILE_DESCRIPTOR_OUT = 1;

    public const FILE_DESCRIPTOR_ERR = 2;

    public const REDIRECT_LEFT = '>&';

    public const REDIRECT_RIGHT = '<&';

    public const OPEN_FILEDESCRIPTOR_RW = '<>';

    public const ERR_TO_OUT_REDIRECT = self::FILE_DESCRIPTOR_ERR . self::REDIRECT_LEFT . self::FILE_DESCRIPTOR_OUT;

    public const HERE_STRING = '<<<';

    public const HERE_DOCUMENT = '<<';

    public const APPEND_STDOUT_ERR = '&>>';
}
