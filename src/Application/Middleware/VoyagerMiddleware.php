<?php

namespace Graphette\Graphette\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VoyagerMiddleware extends RouterMiddleware {

    public function getRoute(): string
    {
        return '/voyager';
    }

    protected function invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        // Load and echo the contents of a .phtml file
        ob_start();
        $uri = $request->getUri()->getHost();
        include __DIR__ . '/../template/voyager.phtml';
        $content = ob_get_clean();

        $stream = $response->getBody();
        $stream->write($content);

        return $response;
    }
}
