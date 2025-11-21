<?php

namespace Graphette\Graphette\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TracyInitMiddleware extends RouterMiddleware
{
    public function getRoute(): string
    {
        return '/tracy-init';
    }


    protected function invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        // todo only debug mode and make safer (consider latte?)
        $content = \Tracy\Helpers::capture(static function (): void {
            \Tracy\Debugger::renderLoader();
        });

        $stream = $response->getBody();
        $stream->write($content);

        return $response;
    }


}
