<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

interface ShellInterface
{
    /**
     * @internal - debug method
     * @return array<array-key, mixed>
     */
    public function __toArray(): array;

    public function __toString(): string;
}
