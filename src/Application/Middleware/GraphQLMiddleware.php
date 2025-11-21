<?php

namespace Graphette\Graphette\Application\Middleware;

use Contributte\Middlewares\IMiddleware;
use Contributte\Psr7\NullStream;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\Helper;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Graphette\Graphette\Application\ServerConfigProvider;
use Graphette\Graphette\Resolving\DefaultFieldResolver;
use Graphette\Graphette\Schema\SchemaBuilder;
use Violet\StreamingJsonEncoder\JsonStream;

class GraphQLMiddleware implements IMiddleware {

    private ServerConfigProvider $serverConfigProvider;

    public function __construct(
        ServerConfigProvider $serverConfigProvider,
    ) {
        $this->serverConfigProvider = $serverConfigProvider;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        callable               $next
    ): ResponseInterface {
        if (empty($request->getParsedBody())) {
            // $request doesn't parse object for some reason, has to be done manually here
            $request = $request->withParsedBody(\json_decode((string) $request->getBody(), true));
        }

        $server = new StandardServer($this->serverConfigProvider->getServerConfig());
        // todo promise?
        $response = $server->processPsrRequest($request, $response, $response->getBody());

        return $next($request, $response);
    }


}
