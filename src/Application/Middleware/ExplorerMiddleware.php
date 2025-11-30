<?php

namespace Graphette\Graphette\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExplorerMiddleware extends RouterMiddleware
{
    public function getRoute(): string
    {
        return '/explorer';
    }


    protected function invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        // todo only debug mode and make safer (consider latte?)

        // Load and echo the contents of a .phtml file
        ob_start();
        $uri = $request->getUri()->getHost();
        $uri = 'http://' . $uri . '/gql';
        include __DIR__ . '/../template/explorer.phtml';
        $content = ob_get_clean();

        $stream = $response->getBody();
        $stream->write($content);

        return $response;
    }


}
