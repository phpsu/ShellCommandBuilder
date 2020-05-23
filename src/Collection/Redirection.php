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
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create($value, $toLeft ? RedirectOperator::REDIRECT_LEFT : RedirectOperator::REDIRECT_RIGHT);
        return $redirect;
    }

    public static function redirectErrorToOutput(): self
    {
        $redirect = new self();
        $redirect->tuple = CollectionTuple::create('', RedirectOperator::ERR_TO_OUT_REDIRECT);
        return $redirect;
    }
}
