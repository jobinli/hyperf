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
use Hyperf\Event\ListenerData;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Utils\Str;
use Psr\EventDispatcher\ListenerProviderInterface;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class ListernersCommand extends InfoCommand
{
    private $globalMiddlewares;

    private $serverName;

    private $filterPath;

    public function __construct()
    {
        parent::__construct('info:listeners');
        $this->setDescription('show the listeners info');
    }

    public function showInfo()
    {
        $this->prepareResult();
    }

    public function prepareResult(): void 
    {
        $eventDispatcher = $this->getContainer()->get(ListenerProviderInterface::class);
        $headers = ['event', 'listener'];
        $rows = [];

        /** @var $listener ListenerData */
        foreach ($eventDispatcher->listeners as $listener) {
            $event = $listener->event;
            $listenerName = get_class($listener->listener[0]);
            $listenerMethod = isset($listener->listener[1]) ? ('@' . $listener->listener[1]) : '';

            if (Str::startsWith($listenerName, 'Hyperf') && !$this->input->getOption('all')) {
                continue;
            }
            
            if ($this->input->getOption('event') && !Str::contains($event, [$this->input->getOption('event')])) {
                continue;
            }

            if ($this->input->getOption('listener') && !Str::contains($listenerName, [$this->input->getOption('listener')])) {
                continue;
            }

            if (!isset($rows[$listener->event])) {
                $rows[$event] = [
                    $event,
                    $listenerName . $listenerMethod
                ];
            } else {
                $rows[$event][1] .= (PHP_EOL . $listenerName . $listenerMethod);
            }
        }
        $this->output->table($headers, array_values($rows));
    }

    public function getArguments()
    {
        return [];
    }

    public function getOptions()
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'show all listeners including the core of hyperf.'],
            ['event', 'e', InputOption::VALUE_OPTIONAL, 'filter event to show.'],
            ['listener', 'l', InputOption::VALUE_OPTIONAL, 'filter listener to show.'],
        ];
    }

}
