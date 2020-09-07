<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder;

trait ShellConditional
{
    public function if(bool $condition, callable $callback, callable $alternativeCallback = null): self
    {
        if ($condition) {
            $result = $callback($this);
            assert($result instanceof self);
            return $result;
        }
        if ($alternativeCallback) {
            $alternativeResult = $alternativeCallback($this);
            assert($alternativeResult instanceof self);
            return $alternativeResult;
        }
        return $this;
    }

    public function ifThis(callable $callOnThis, callable $callback, callable $alternativeCallback = null): self
    {
        return $this->if($callOnThis($this) === true, $callback, $alternativeCallback);
    }
}
