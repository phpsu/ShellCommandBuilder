<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Collection;

use PHPSu\ShellCommandBuilder\Definition\RedirectOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class Redirection extends AbstractCollection
{
    /**
     * @param string|ShellInterface $value
     * @param bool $append
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectOutput($value, bool $append): self
    {
        $this->tuple = CollectionTuple::create($value, $append ? RedirectOperator::STDOUT_LEFT_APPEND : RedirectOperator::STDOUT_LEFT_INSERT);
        return $this;
    }

    /**
     * @param string|ShellInterface $value
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectInput($value): self
    {
        $this->tuple = CollectionTuple::create($value, RedirectOperator::STDIN_RIGHT);
        return $this;
    }

    /**
     * @param string|ShellInterface $value
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectError($value): self
    {
        $this->tuple = CollectionTuple::create($value, RedirectOperator::FILE_DESCRIPTOR_ERR . RedirectOperator::STDOUT_LEFT_INSERT);
        return $this;
    }

    /**
     * @param string|ShellInterface $value
     * @param bool $toLeft
     * @return $this
     * @throws ShellBuilderException
     */
    public function redirectBetweenFiles($value, bool $toLeft): self
    {
        $this->tuple = CollectionTuple::create($value, $toLeft ? RedirectOperator::REDIRECT_LEFT : RedirectOperator::REDIRECT_RIGHT);
        return $this;
    }

    public function redirectErrorToOutput(): self
    {
        $this->tuple = CollectionTuple::create('', RedirectOperator::ERR_TO_OUT_REDIRECT);
        return $this;
    }
}
