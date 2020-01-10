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
use Hyperf\Di\Annotation\AspectCollector;

/**
 * @Command
 */
class AspectsCommand extends InfoCommand
{
    public function __construct()
    {
        parent::__construct('info:aspects');
        $this->setDescription('show the aspects info');
    }

    public function showInfo()
    {
        $result = $this->prepareResult();
        $this->dump($result);
    }

    /**
     * Prepare the result, maybe this result not just use in here.
     */
    public function prepareResult(): array
    {
        $result = [];
        $aspects = AspectCollector::list();
        foreach ($aspects as $type => $collections) {
            foreach ($collections as $aspect => $target) {
                $result[$aspect][$type] = $target;
            }
        }
        return $result;
    }

    public function getArguments()
    {
        return [];
    }

    public function getOptions()
    {
        return [];
    }

    /**
     * Dump to the console according to the prepared result.
     */
    private function dump(array $result): void
    {
        foreach ($result as $aspect => $targets) {
            $this->output->writeln("<info>{$aspect}</info>");
            if (isset($targets['annotations'])) {
                $this->output->writeln($this->tab('Annotations:'));
                foreach ($targets['annotations'] ?? [] as $annotation) {
                    $this->output->writeln($this->tab($annotation ?? '', 2));
                }
            }
            if (isset($targets['classes'])) {
                $this->output->writeln($this->tab('Classes:'));
                foreach ($targets['classes'] ?? [] as $class) {
                    $this->output->writeln($this->tab($class ?? '', 2));
                }
            }
        }
    }
}
