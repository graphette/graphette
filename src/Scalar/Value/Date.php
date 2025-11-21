<?php

namespace Graphette\Graphette\Scalar\Value;

use Nette\Utils\Strings;

class Date {

    private string $date;
    private int $year;
    private int $month;
    private int $day;

    private const FORBIDDEN_TIME_FORMAT_LETTERS = ['a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v', 'e', 'I', 'P', 'p', 'T', 'Z', 'c', 'r', 'U'];

    public function __construct(\DateTimeInterface|string $date, string $format = 'Y-m-d') {
//		self::checkFormat($format);

        if (is_string($date)) {
            $this->date = $date;
            $dateTime = \DateTime::createFromFormat($format, $this->date);
        } else {
            $this->date = $date->format($format);
            $dateTime = $date;
        }

        $this->year = (int)$dateTime->format('Y');
        $this->month = (int)$dateTime->format('m');
        $this->day = (int)$dateTime->format('d');

        // todo Ensure that timezones are not messing this up
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function format(string $format): string
    {
        self::checkFormat($format);

        return \DateTime::createFromFormat($format, $this->date)->format($format);
    }

    public static function createFromFormat(string $format, string $datetime): self {
        return new self($datetime, $format);
    }

    private static function checkFormat($format): void {
        //make sure format does not contain any time format characters
        $containsTimeFormat = (bool) Strings::match($format, '/[' . implode('', self::FORBIDDEN_TIME_FORMAT_LETTERS) . ']/', PREG_OFFSET_CAPTURE);

        if ($containsTimeFormat === true) {
            throw new \InvalidArgumentException('Date format cannot contain any time format characters.');
        }
    }
}
