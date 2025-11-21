<?php

namespace Graphette\Graphette\Scalar\Value;

use Nette\Utils\Strings;

class Time {

	private \DateTimeInterface $dateTime;

	private const FORBIDDEN_DATE_FORMAT_LETTERS = ['d', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'X', 'x', 'Y', 'y'];

	public function __construct(\DateTimeInterface|string $date, string $format = 'H:i:s') {
		self::checkFormat($format);

		if (is_string($date)) {
			$this->dateTime = \DateTime::createFromFormat($format, $date);
		} else {
			$this->dateTime = $date;
		}

		// todo Ensure that timezones are not messing this up
	}

	public function getHour(): int {
		return (int) $this->dateTime->format('H');
	}

	public function getMinute(): int {
		return (int) $this->dateTime->format('i');
	}

	public function getSecond(): int {
		return (int) $this->dateTime->format('s');
	}

	public function format(string $format): string {
		self::checkFormat($format);

		return $this->dateTime->format($format);
	}

	public static function createFromFormat(string $format, string $datetime): self {
		return new self($datetime, $format);
	}

	private static function checkFormat($format): void {
		//make sure format does not contain any time format characters
		$containsTimeFormat = (bool)Strings::match($format, '/[' . implode('', self::FORBIDDEN_DATE_FORMAT_LETTERS) . ']/', PREG_OFFSET_CAPTURE);

		if ($containsTimeFormat === true) {
			throw new \InvalidArgumentException('Time format cannot contain any date format characters.');
		}
	}

}
