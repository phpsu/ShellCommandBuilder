<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\RedirectOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * @psalm-internal PHPSu\ShellCommandBuilder
 */
final class Redirection extends AbstractCollection
{
    /**
     * @param string|ShellInterface $value
     * @param bool $append
     * @return $this
     * @throws ShellBuilderException
     */
    public static function redirectOutput($value, bool $append): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, $append ? RedirectOperator::STDOUT_LEFT_APPEND : RedirectOperator::STDOUT_LEFT_INSERT);
        return $redirect;
    }

    /**
     * @param string|ShellInterface $value
     * @return $this
     * @throws ShellBuilderException
     */
    public static function redirectInput($value): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, RedirectOperator::STDIN_RIGHT);
        return $redirect;
    }

    /**
     * @param string|ShellInterface $value
     * @return $this
     * @throws ShellBuilderException
     */
    public static function redirectError($value): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, RedirectOperator::FILE_DESCRIPTOR_ERR . RedirectOperator::STDOUT_LEFT_INSERT);
        return $redirect;
    }

    /**
     * @param string|ShellInterface $value
     * @param bool $toLeft
     * @return $this
     * @throws ShellBuilderException
     */
    public static function redirectBetweenFiles($value, bool $toLeft): self
    {
        return self::redirectBetweenDescriptors($value, $toLeft);
    }

    /**
     * @param string|ShellInterface $value
     * @param bool $toLeft
     * @param int|null $firstDescriptor
     * @param int|null $secondDescriptor
     * @return static
     * @throws ShellBuilderException
     */
    public static function redirectBetweenDescriptors($value, bool $toLeft, int $firstDescriptor = null, int $secondDescriptor = null): self
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
