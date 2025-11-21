<?php

declare(strict_types=1);

namespace Graphette\Graphette\Application\Middleware;

use Contributte\Middlewares\IMiddleware;
use Nette\Http\Url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class RouterMiddleware implements IMiddleware
{
    /**
     * The following formats for getRoute() are currently supported:
     * [protocol]://[hostname]:[port]/[path]
     * [protocol]://[hostname]/[path]
     * [hostname]:[port]/[path]
     * [hostname]/[path]
     * /[path]
     *
     * Examples:
     * https://mydomain.com:8080/invoices
     * https://mydomain.com/invoices
     * mydomain.com:8080/invoices
     * mydomain.com/invoices
     * /invoices
     */
    abstract public function getRoute(): string;

    abstract protected function invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface;

    protected function lazyLoadDependencies(): void
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        if ($this->match($request, $this->getRoute())) {
            $this->lazyLoadDependencies();

            return $this->invoke($request, $response);
        }

        return $next($request, $response);
    }

    protected function match(ServerRequestInterface $request, string $route): bool
    {
        $requestUrl = new Url(rtrim((string)$request->getUri(), '/'));

        $requestPath = $requestUrl->getPath();
        $requestPathExploded = explode('/', $requestPath);
        $requestBaseUri = '/' . $requestPathExploded[1];
        $requestBaseUrl = (clone $requestUrl)->setPath($requestBaseUri);

        $routeUrl = new Url(rtrim($route, '/'));

        if ($routeUrl->getScheme() === '') {
            $requestUrl->setScheme('');
        }

        if (($routeUrl->getScheme() !== '' && $routeUrl->getHost() !== '')) {
            return $routeUrl->getAbsoluteUrl() === $requestUrl->getAbsoluteUrl();
        } elseif ($routeUrl->getHost() !== '' && $routeUrl->getPort() !== null) {
            return $routeUrl->getHost() === $requestUrl->getHost() && $routeUrl->getPort() === $requestUrl->getPort()
                && $routeUrl->getPath() === $requestUrl->getPath();
        } elseif ($routeUrl->getHost() !== '') {
            return $routeUrl->getHost() === $requestUrl->getHost() && $routeUrl->getPath() === $requestUrl->getPath()
                && $routeUrl->getPort() === $requestUrl->getPort();
        } else {
            return $routeUrl->getPath() === $requestBaseUrl->getPath()
                || $routeUrl->getPath() === $requestBaseUrl->getHost()
                . ($requestUrl->getPort() ? ':' . $requestBaseUrl->getPort() : '')
                . $requestBaseUrl->getPath();
        }
    }

    protected function getRouteParameter(ServerRequestInterface $request, string $middlewareUri): ?string
    {
        if (preg_match('/^\/' . $middlewareUri . '\/(.*)$/', $request->getUri()->getPath(), $matches) !== 1) {
            return null;
        }

        return $matches[1] ?? null;
    }
}
