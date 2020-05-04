<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

interface ShellInterface
{
    /** @internal - debug method */
    public function __toArray(): array;

    public function __toString(): string;
}
