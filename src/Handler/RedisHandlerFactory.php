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
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class RedisHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $connection = $config->get('session.options.redis');
        $gcMaxLifetime = $config->get('session.options.gc_maxlifetime', 1440);
        $redisFactory = $container->get(RedisFactory::class);
        $redis = $redisFactory->get($connection);
        return new RedisHandler($redis, $gcMaxLifetime);
    }
}
