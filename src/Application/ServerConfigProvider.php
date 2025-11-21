<?php

namespace Graphette\Graphette\Application;

use GraphQL\Error\DebugFlag;
use GraphQL\Server\ServerConfig;
use Nette\Http\Request;
use Graphette\Graphette\Error\DefaultErrorFormatter;
use Graphette\Graphette\Resolving\DefaultFieldResolver;
use Graphette\Graphette\Schema\SchemaBuilder;

class ServerConfigProvider {

    private bool $debugMode;

    private bool $tracyExceptionWithHeader;

    private SchemaBuilder $schemaBuilder;

    private DefaultFieldResolver $defaultFieldResolver;

    private DefaultErrorFormatter $defaultErrorFormatter;

    private Request $request;

    public function __construct(
        bool $debugMode,
        bool $tracyExceptionWithHeader,
        SchemaBuilder $schemaBuilder,
        DefaultFieldResolver $defaultFieldResolver,
        DefaultErrorFormatter $defaultErrorFormatter,
        Request $request,
    ) {
        $this->schemaBuilder = $schemaBuilder;
        $this->defaultFieldResolver = $defaultFieldResolver;
        $this->debugMode = $debugMode;
        $this->tracyExceptionWithHeader = $tracyExceptionWithHeader;
        $this->defaultErrorFormatter = $defaultErrorFormatter;
        $this->request = $request;
    }

    public function getServerConfig(): ServerConfig {
        if (
            ($this->debugMode === true && $this->tracyExceptionWithHeader === false)
            || ($this->debugMode === true && $this->tracyExceptionWithHeader === true && $this->request->getHeader('X-Debug-TracyException')!==null)
        ) {
            $debugFlag = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
        } else {
            $debugFlag = DebugFlag::NONE;
        }

        return ServerConfig::create()
            ->setDebugFlag($debugFlag)
            ->setSchema($this->schemaBuilder->build())
            ->setFieldResolver([$this->defaultFieldResolver, 'defaultFieldResolver'])
            ->setErrorFormatter([$this->defaultErrorFormatter, 'formatError'])
            ;
    }

}
