<?php

namespace Graphette\Graphette\Scalar\Value;

class Decimal implements \Stringable, FromStringCreatable
{
    private string $number;

    public function __construct(string $numberString)
    {
        if (self::isValidDecimal($numberString) === false) {
            throw new \UnexpectedValueException('Invalid number format.');
        }

        $this->number = $numberString;
    }

    public static function createFromString(string $value): static
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->number;
    }

    public static function isValidDecimal(string $numberString): bool
    {
        // TODO improve regex to handle edge cases like -0.00
        if (preg_match('/^(?!-0$)-?(0|[1-9]\d*)(\.\d+)?$/', $numberString)) {
            return true;
        }

        return false;
    }

    public function getDecimalPlaces(): int
    {
        $numberExploded = explode('.', $this->number);

        if (count($numberExploded) === 2) {
            return strlen($numberExploded(1));
        }

        return 0;
    }
}
