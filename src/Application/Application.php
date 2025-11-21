<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Application;


use GraphQL\Error\DebugFlag;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Nette\Http\Request;
use Graphette\Graphette\Resolving\DefaultFieldResolver;
use Graphette\Graphette\Schema\SchemaBuilder;
use Graphette\Graphette\TypeRegistry\TypeRegistryBuilder;
use Graphette\Graphette\TypeRegistry\TypeRegistryBuilderFactory;
use Graphette\Graphette\TypeRegistry\TypeRegistryLoader;

class Application {

    private Request $request;

    private ServerConfigProvider $serverConfigProvider;

    public function __construct(
        Request              $request,
        ServerConfigProvider $serverConfigProvider,
    ) {
        $this->request = $request;
        $this->serverConfigProvider = $serverConfigProvider;
    }

    public function run(): void {
        // todo implement proper routing
        if ($this->request->getUrl()->getPath() === '/graphiql') {
            // Load and echo the contents of a .phtml file
            ob_start();
            include __DIR__ . '/template/graphiql.phtml';
            $content = ob_get_clean();
            echo $content;
            return;
        }


        $server = new StandardServer($this->serverConfigProvider->getServerConfig());

        $server->handleRequest();

    }

}
