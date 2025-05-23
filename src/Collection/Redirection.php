<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\RedirectOperator;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class Redirection extends AbstractCollection
{
    public static function redirectOutput(ShellInterface|string $value, bool $append): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, $append ? RedirectOperator::STDOUT_LEFT_APPEND : RedirectOperator::STDOUT_LEFT_INSERT);
        return $redirect;
    }

    public static function redirectInput(ShellInterface|string $value): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, RedirectOperator::STDIN_RIGHT);
        return $redirect;
    }

    public static function redirectError(ShellInterface|string $value): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, RedirectOperator::FILE_DESCRIPTOR_ERR . RedirectOperator::STDOUT_LEFT_INSERT);
        return $redirect;
    }

    public static function redirectBetweenFiles(ShellInterface|string $value, bool $toLeft): self
    {
        return self::redirectBetweenDescriptors($value, $toLeft);
    }

    public static function redirectBetweenDescriptors(ShellInterface|string $value, bool $toLeft, ?int $firstDescriptor = null, ?int $secondDescriptor = null): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, sprintf(
            '%s%s%s',
            $firstDescriptor ?: '',
            $toLeft ? RedirectOperator::REDIRECT_LEFT : RedirectOperator::REDIRECT_RIGHT,
            $secondDescriptor ?: ''
        ));
        return $redirect;
    }

    public static function redirectErrorToOutput(): self
    {
        return self::redirectBetweenDescriptors('', true, 2, 1);
    }
}
