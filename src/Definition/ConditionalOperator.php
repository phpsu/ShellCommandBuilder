<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Definition;

/**
 * @see https://www.gnu.org/software/bash/manual/html_node/Bash-Conditional-Expressions.html
 * Class ConditionalOperator
 * @package PHPSu\ShellCommandBuilder\Definition
 */
final class ConditionalOperator
{
    public const BRACKET_LEFT = '[';

    public const BRACKET_RIGHT = ']';

    public const BRACKET_LEFT_BASH = '[[';

    public const BRACKET_RIGHT_BASH = ']]';

    /* FILE OPERATOR */
    public const FILE_EXISTS = '-a';

    public const FILE_EXISTS_BLOCK_SPECIAL = '-b';

    public const FILE_EXISTS_CHARACTER_SPECIAL = '-c';

    public const FILE_EXISTS_IS_DIRECTORY = '-d';

    public const FILE_EXISTS_REGULAR_FILE = '-f';

    public const FILE_EXISTS_HAS_SET_GROUP_ID = '-g';

    public const FILE_EXSITS_SYMBOLIC_LINK = '-h';

    public const FILE_EXSITS_STICKY_BIT = '-k';

    public const FILE_EXSITS_NAMED_PIPE = '-p';

    public const FILE_EXSITS_READABLE = '-r';

    public const FILE_EXSITS_NOT_EMPTY = '-s';

    public const FILE_EXSITS_OPEN_REFERING_TO_TERMINAL = '-t';

    public const FILE_EXSITS_HAS_SET_USER_ID = '-u';

    public const FILE_EXSITS_WRITABLE = '-w';

    public const FILE_EXSITS_EXECUTABLE = '-x';

    public const FILE_EXSITS_OWNED_BY_GROUP_ID = '-G';

    public const FILE_EXSITS_MODIFIED_SINCE_LAST_READ = '-N';

    public const FILE_EXSITS_OWNED_BY_USER_ID = '-O';

    public const FILE_EXSITS_IS_SOCKET = '-S';

    public const FILE_REFERS_TO_SAME_DEVICE = '-ef';

    public const FILE_IS_NEWER_THAN = '-nt';

    public const FILE_IS_OLDER_THAN = '-ot';

    /* SHELL OPERATOR */
    public const SHELL_OPTNAME_ENABLED = '-o';

    public const SHELL_VARNAME_SET = '-v';

    public const SHELL_VARNAME_SET_NAMED_REFERENCE = '-R';

    /* ARITHMETIC / STRING OPERATORS */
    public const STRING_LENGHT_ZERO = '-z';

    public const STRING_LENGHT_NOT_ZERO = '-n';

    /** @var string POSIX-compatible, should be used for the `test` command */
    public const STRING_EQUAL = '=';

    /** @var string this is used within [[ ]] */
    public const STRING_EQUAL_BASH = '==';

    public const STRING_NOT_EQUAL = '!=';

    public const STRING_SORTS_BEFORE = '<';

    public const STRING_SORTS_AFTER = '>';

    public const ARTITH_EQUAL = '-eq';

    public const ARTITH_NOT_EQUAL = '-ne';

    public const ARTITH_LESS_THAN = '-lt';

    public const ARTITH_GREATER_THAN = '-gt';

    public const ARTITH_LESS_EQUAL = '-le';

    public const ARTITH_GREATER_EQUAL = '-ge';
}
