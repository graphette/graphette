<?php

namespace Graphette\Graphette\Application\Middleware;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphiQLMiddleware extends RouterMiddleware {

    public function getRoute(): string
    {
        return '/graphiql';
    }

    protected function invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        // Load and echo the contents of a .phtml file
        ob_start();
        $uri = $request->getUri()->getHost();
        include __DIR__ . '/../template/graphiql.phtml';
        $content = ob_get_clean();

        $stream = $response->getBody();
        $stream->write($content);

        return $response;
    }
}
