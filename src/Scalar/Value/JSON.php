<?php

namespace Graphette\Graphette\Scalar\Value;

use Graphette\Graphette\Scalar\Value\Exception\InvalidURL;

class JSON implements \Stringable, FromStringCreatable, \ArrayAccess
{

    private array $json;

    public function __construct(array $json)
    {
        $this->json = $json;
    }

    public static function createFromString(string $value): static
    {
        $jsonDecoded = json_decode($value, true);
        return new static($jsonDecoded);
    }

    // todo might be usefull to have a static method for validation against Nette/Schema?


    public function __toString(): string
    {
        return json_encode($this->json, JSON_THROW_ON_ERROR);
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->json[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->json[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->json[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return isset($this->json[$offset]) ? $this->json[$offset] : null;
    }
}
