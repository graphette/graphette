<?php

namespace Graphette\Graphette\Application\Middleware;

use Contributte\Middlewares\IMiddleware;
use GraphQL\Upload\UploadMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadMiddlewareAdapter implements IMiddleware {

    private UploadMiddleware $uploadMiddleware;

    public function __construct() {
        $this->uploadMiddleware = new UploadMiddleware();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        callable               $next
    ): ResponseInterface {
        $request = $this->uploadMiddleware->processRequest($request);

        return $next($request, $response);
    }


}
