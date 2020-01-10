<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Devtool\Info;

use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class RoutersCommand extends InfoCommand
{
    private $globalMiddlewares;

    private $serverName;

    private $filterPath;

    public function __construct()
    {
        parent::__construct('info:routes');
        $this->setDescription('show the routes info');
    }

    public function showInfo()
    {
        $serverType = $this->input->getOption('serverType');

        $this->filterPath = $this->input->getArgument('path') ?: [];
        $this->serverName = $this->input->getOption('serverName');
        $this->globalMiddlewares = $this->getContainer()->get(ConfigInterface::class)->get('middlewares.' . $this->serverName) ?: [];

        $result = $this->prepareResult($serverType);
        $this->dump($result);
    }

    public function prepareResult($serverType): array
    {
        $factory = $this->getContainer()->get(DispatcherFactory::class);
        [$staticRouters, $regexRouters] = $factory->getRouter($serverType)->getData();

        $result = [];

        // handle static routers
        foreach ($staticRouters as $method => $routers) {
            foreach ($routers as $router) {
                $row = $this->formatRow($router, $method);
                if (! $row) {
                    continue;
                }
                $result[] = $row;
            }
        }

        // handle regex routers
        foreach ($regexRouters as $method => $routers) {
            $routeMaps = array_column($routers, 'routeMap');
            foreach ($routeMaps as $regexRoute) {
                foreach ($regexRoute as $router) {
                    $row = $this->formatRow($router[0], $method);
                    if (! $row) {
                        continue;
                    }
                    $result[] = $row;
                }
            }
        }

        return $result;
    }

    public function getArguments()
    {
        return [
            ['path', InputOption::VALUE_OPTIONAL, 'The url path want show.'],
        ];
    }

    public function getOptions()
    {
        return [
            ['serverType', 't', InputOption::VALUE_OPTIONAL, 'The type of server want show routes list, http or jsonrpc', 'http'],
            ['serverName', 's', InputOption::VALUE_OPTIONAL, 'The name of server want show routes list.', 'http'],
        ];
    }

    /**
     * row formatter.
     * @param Handler $router
     * @param string $method
     * @return array|null
     */
    private function formatRow(Handler $router, string $method): ?array
    {
        $path = $router->route;
        $callback = $router->callback;
        $pathMiddleware = MiddlewareManager::get($this->serverName, $path, $method);
        $middlewares = implode(PHP_EOL, array_unique(array_merge($this->globalMiddlewares, $pathMiddleware)));

        if (! empty($this->filterPath) && ! in_array($path, $this->filterPath)) {
            return null;
        }

        if (is_array($callback)) {
            $callback = implode('@', $callback);
        } elseif (is_callable($callback)) {
            $callback = 'Anonymous function';
        }

        return [$path, $method, $middlewares, $callback];
    }

    private function dump(array $result): void
    {
        $routes = $result;

        if (empty($routes)) {
            $this->output->error('No routes.');
            return;
        }

        $headers = ['path', 'method', 'middlewares', 'callback'];
        $this->output->table($headers, $routes);
    }
}
