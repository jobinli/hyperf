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

use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class InfoCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface|SymfonyStyle
     */
    protected $output;

    public function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);

        $this->showInfo();

        return 0;
    }

    abstract public function showInfo();

    abstract public function getArguments();

    abstract public function getOptions();

    protected function tab(string $append = '', int $int = 1, int $length = 4)
    {
        return str_repeat(' ', $int * $length) . $append;
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
