<?php

namespace Graphette\Graphette\Scalar\Value;

use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Graphette\Graphette\Scalar\Value\Exception\InvalidEmail;

class Email implements \Stringable, FromStringCreatable {

    use CreateFromString;

    private string $email;

    /**
     * @throws InvalidEmail
     */
    public function __construct(string $email) {
        if (self::isValid($email) === false) {
            throw new InvalidEmail('Given email `' . $email . ' is not valid');
        }

        $this->email = $email;
        $this->normalize();
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getLocalPart(): string
    {
        [$localPart,] = explode('@', $this->email, 2);
        return $localPart;
    }

    public function getDomainPart(): string
    {
        [, $domainPart] = explode('@', $this->email, 2);
        return $domainPart;
    }

    public function __toString(): string {
        return $this->email;
    }

    private function normalize(): void {
        $this->email = Strings::lower($this->email);
    }

    public static function isValid(string $email): bool {
        return Validators::isEmail($email);
    }

}
