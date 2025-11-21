<?php

declare(strict_types=1);

namespace Graphette\Graphette\Scalar\Value;

use Graphette\Graphette\Scalar\Value\Exception\InvalidURL;
use Nette\Http\Url as NetteUrl;
use Nette\Utils\Validators;

class URL implements \Stringable, FromStringCreatable
{
    use CreateFromString;

    private string $url;

    /**
     * @throws InvalidURL
     */
    public function __construct(string $url)
    {
        self::checkUrl($url);
        $this->url = $url;
    }

    /**
     * @throws InvalidURL
     */
    public static function checkUrl(string $url): void
    {
        if (Validators::isUrl($url)) {
            return;
        }

        throw new InvalidURL('Provided URL is not valid: '.$url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function toNetteUrl(): NetteUrl
    {
        return new NetteUrl($this->url);
    }
}
