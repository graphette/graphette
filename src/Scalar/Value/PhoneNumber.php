<?php

namespace Graphette\Graphette\Scalar\Value;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Graphette\Graphette\Scalar\Value\Exception\InvalidPhoneNumber;

class PhoneNumber implements \Stringable, FromStringCreatable
{
    use CreateFromString;

	private string $phoneNumber;

	/**
	 * @throws InvalidPhoneNumber
	 */
	public function __construct(string $phoneNumber)
	{
		self::checkPhoneNumber($phoneNumber);
		$this->phoneNumber = PhoneNumberUtil::normalizeDiallableCharsOnly($phoneNumber);
	}

	/**
	 * @throws InvalidPhoneNumber
	 */
	public static function checkPhoneNumber(string $phoneNumberValue): void
	{
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneNumber = null;
		try {
			$phoneNumber = $phoneUtil->parse($phoneNumberValue);
		} catch (NumberParseException) {
		}

		if ($phoneNumber !== null && $phoneUtil->isValidNumber($phoneNumber)) {
			return;
		}

		throw new InvalidPhoneNumber($phoneNumberValue . ' is not a valid phone number.');
	}

	public function getPhoneNumber(): string
	{
		return $this->phoneNumber;
	}

    public function __toString()
    {
        return $this->getPhoneNumber();
    }
}
