<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Definition;

final class GroupType
{
    public const NO_GROUP = 0;

    public const SUBSHELL_GROUP = 1;

    public const SAMESHELL_GROUP = 2;
}
