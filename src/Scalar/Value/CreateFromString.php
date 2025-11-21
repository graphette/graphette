<?php

namespace Graphette\Graphette\Scalar\Value;

trait CreateFromString
{

    public static function createFromString(string $value): static
    {
        return new static($value);
    }

}
