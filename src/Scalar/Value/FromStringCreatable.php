<?php

namespace Graphette\Graphette\Scalar\Value;

interface FromStringCreatable
{

    public static function createFromString(string $value): static;

}
