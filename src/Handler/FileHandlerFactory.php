<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hf\Session\Handler;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

class FileHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $path = $config->get('session.options.save_path');
        $minutes = $config->get('session.options.gc_maxlifetime', 1440);
        if (! $path) {
            throw new \InvalidArgumentException('Invalid session path.');
        }
        return new FileHandler($container->get(Filesystem::class), $path, $minutes);
    }
}
